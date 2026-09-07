<?php

use Illuminate\Support\Facades\Route;
use App\Models\Order;
use App\Models\PhotoType;
use App\Models\PaperType;
use App\Models\Package;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

Route::get('/', function () {
    return view('layouts/app');
});

/*
 * Rutas para catálogos (usadas en OrderModal y PhotoTypeManager)
 */
Route::get('/photo-types', function () {
    return PhotoType::all(['id', 'name', 'base_price']);
});

Route::post('/photo-types', function (Request $request) {
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
        ]);
        $photoType = PhotoType::create($validated);
        return response()->json($photoType, 201);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::put('/photo-types/{id}', function (Request $request, $id) {
    $photoType = PhotoType::findOrFail($id);
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'base_price' => 'required|numeric|min:0',
    ]);
    $photoType->update($validated);
    return response()->json($photoType);
});

Route::delete('/photo-types/{id}', function ($id) {
    $photoType = PhotoType::findOrFail($id);
    $photoType->delete();
    return response()->json(['message' => 'Eliminado']);
});

Route::get('/paper-types', function () {
    return PaperType::all(['id', 'name', 'extra_price']);
});

Route::get('/packages', function () {
    return Package::with('school:id,name')
        ->get(['id', 'school_id', 'package_number', 'name', 'total_price']);
});

Route::get('/customers', function () {
    return Customer::select('id', 'name', 'phone', 'email')
        ->orderBy('name')
        ->get();
});

/*
 * Tablero de órdenes
 */
Route::get('/orders-table', function () {
    return Order::with([
        'customer:id,name,phone',
        'packages:id,package_number,name,total_price',
        'orderPhotoTypes.photoType:id,name,base_price',
        'orderPhotoTypes.paperType:id,name,extra_price',
    ])
    ->orderBy('created_at', 'desc')
    ->get(['id', 'customer_id', 'total', 'balance_due', 'pose_number', 'notes',
           'estimated_delivery_date', 'actual_delivery_date', 'status',
           'signature_id', 'created_at']);
});

/*
 * Crear una nueva orden
 */
Route::post('/orders', function (Request $request) {
    $validated = $request->validate([
        'customer_id' => 'nullable|exists:customers,id',
        'new_customer' => 'nullable|array',
        'new_customer.name' => 'required_with:new_customer|string|max:255',
        'new_customer.phone' => 'nullable|string|max:20',
        'new_customer.email' => 'nullable|email|max:255|unique:customers,email',

        'new_customer.phone' => 'nullable|digits:10',
        'new_customer.email' => 'nullable|email|max:255|unique:customers,email',

        'contact_update.phone' => 'nullable|digits:10',
        'contact_update.email' => [
            'nullable', 'email', 'max:255',
            Rule::unique('customers', 'email')->ignore($request->customer_id),
        ],

        'estimated_delivery_date' => 'required|date|after_or_equal:today',
        'pose_number' => 'nullable|integer|min:1',
        'notes' => 'nullable|string|max:1000',
        'photo_types' => 'required|array|min:1',
        'photo_types.*.photo_type_id' => 'required|exists:photo_types,id',
        'photo_types.*.quantity' => 'required|integer|min:1',
        'photo_types.*.format' => ['required', Rule::in(['color', 'bn'])],
        'photo_types.*.paper_type_id' => 'nullable|exists:paper_types,id',
        'package_ids' => 'nullable|array',
        'package_ids.*' => 'exists:packages,id',
        'payments' => 'required|array|min:1',
        'payments.*.amount' => 'required|numeric|min:0.01',
        'payments.*.payment_method' => 'nullable|string|max:50',
    ]);

    try {
        DB::beginTransaction();

        $customer = null;

        if ($request->filled('customer_id')) {
            // El recepcionista sí seleccionó del dropdown
            $customer = Customer::findOrFail($request->customer_id);
            if ($request->filled('contact_update')) {
                $customer->update(array_filter([
                    'phone' => $request->input('contact_update.phone'),
                    'email' => $request->input('contact_update.email'),
                ], fn ($value) => $value !== null));
            }

        } elseif ($request->filled('new_customer')) {
            $rawName = trim($request->new_customer['name']);
            $normalizedName = mb_strtolower(preg_replace('/\s+/', ' ', $rawName));
            $customer = Customer::whereRaw('LOWER(name) = ?', [$normalizedName])
                ->lockForUpdate()
                ->first();

            if (!$customer) {
                try {
                    $customer = Customer::create([
                        'name' => $rawName,
                        'phone' => $request->new_customer['phone'] ?? null,
                        'email' => $request->new_customer['email'] ?? null,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                        $customer = Customer::whereRaw('LOWER(name) = ?', [$normalizedName])->first();
                    } else {
                        throw $e;
                    }
                }
            }

        } else {
            return response()->json([
                'message' => 'Debe seleccionar un cliente existente o crear uno nuevo.'
            ], 422);
        }

        $total = 0;
        $photoTypesData = [];
        foreach ($request->photo_types as $line) {
            $photoType = PhotoType::find($line['photo_type_id']);
            $unitPrice = $photoType->base_price ?? 0;
            $subtotal = $unitPrice * $line['quantity'];
            $total += $subtotal;

            $photoTypesData[] = [
                'photo_type_id' => $line['photo_type_id'],
                'quantity' => $line['quantity'],
                'unit_price' => $unitPrice,
                'format' => $line['format'],
                'paper_type_id' => $line['paper_type_id'] ?? null,
            ];
        }

        $packages = [];
        if ($request->filled('package_ids')) {
            $packages = Package::whereIn('id', $request->package_ids)->get();
            foreach ($packages as $pkg) {
                $total += $pkg->total_price;
            }
        }
        $totalPayments = collect($request->payments)->sum('amount');
        if ($totalPayments > $total) {
            DB::rollBack();
            return response()->json([
                'message' => 'La suma de los pagos no puede exceder el total de la orden ($' . number_format($total, 2) . ').'
            ], 422);
        }

        $order = Order::create([
            'customer_id' => $customer->id,
            'total' => $total,
            'balance_due' => $total, // el observer lo corrige en cuanto se registren los pagos
            'pose_number' => $request->pose_number,
            'notes' => $request->notes,
            'estimated_delivery_date' => $request->estimated_delivery_date,
            'actual_delivery_date' => null,
            'status' => 'pending',
            'signature_id' => null,
        ]);

        foreach ($photoTypesData as $lineData) {
            $order->orderPhotoTypes()->create($lineData);
        }

        foreach ($packages as $pkg) {
            $order->packages()->attach($pkg->id, [
                'package_name_snapshot' => $pkg->name ?: ('Paquete ' . $pkg->package_number),
                'price_snapshot' => $pkg->total_price,
            ]);
        }
        // Registrar cada pago capturado. El que complete el total se marca
        // como 'settlement' (liquidación); los anteriores son 'advance' (abono).
        $accumulated = 0;
        foreach ($request->payments as $i => $paymentData) {
            $accumulated += $paymentData['amount'];
            $isSettlement = $accumulated >= $total;

            Payment::create([
                'order_id' => $order->id,
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'] ?? null,
                'type' => $isSettlement ? 'settlement' : 'advance',
                'paid_at' => now(),
                'notes' => 'Pago ' . ($i + 1) . ($isSettlement ? ' (liquidación)' : ' (abono)'),
            ]);
        }

        DB::commit();

        $order->load([
            'customer:id,name,phone',
            'packages:id,package_number,name,total_price',
            'orderPhotoTypes.photoType:id,name,base_price',
            'orderPhotoTypes.paperType:id,name,extra_price',
            'payments',
        ]);
        $order->refresh(); //asegura balance_due actualizado por el observer antes de responder

        return response()->json([
            'message' => 'Orden creada exitosamente',
            'order' => $order,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Error al crear la orden: ' . $e->getMessage(),
        ], 500);
    }
});

/*
 * Registrar un pago adicional sobre una orden existente
 * (para completar el anticipo o para la liquidación al recoger)
 */
Route::post('/orders/{order}/payments', function (Request $request, Order $order) {
    $validated = $request->validate([
        'amount' => 'required|numeric|min:0.01',
        'payment_method' => 'nullable|string|max:50',
        'type' => ['required', Rule::in(['advance', 'settlement'])],
        'notes' => 'nullable|string|max:255',
    ]);

    if ($validated['amount'] > $order->balance_due) {
        return response()->json([
            'message' => 'El monto excede el saldo pendiente de la orden ($' . number_format($order->balance_due, 2) . ').'
        ], 422);
    }

    $payment = Payment::create([
        'order_id' => $order->id,
        'amount' => $validated['amount'],
        'payment_method' => $validated['payment_method'] ?? null,
        'type' => $validated['type'],
        'notes' => $validated['notes'] ?? null,
        'paid_at' => now(),
    ]);

    $order->refresh(); //trae balance_due ya recalculado por el observer

    return response()->json([
        'message' => 'Pago registrado correctamente',
        'payment' => $payment,
        'order_balance_due' => $order->balance_due,
    ], 201);
});

/*
 * Marcar una orden como entregada.
 * Bloquea la entrega si aún tiene saldo pendiente.
 */
Route::patch('/orders/{order}/deliver', function (Order $order) {
    if ($order->balance_due > 0) {
        return response()->json([
            'message' => 'No se puede entregar la orden: tiene un saldo pendiente de $' . number_format($order->balance_due, 2) . '.'
        ], 422);
    }

    $order->update([
        'status' => 'delivered',
        'actual_delivery_date' => now(),
    ]);

    return response()->json(['message' => 'Orden entregada correctamente', 'order' => $order]);
});