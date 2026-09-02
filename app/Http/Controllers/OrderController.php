<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\OrderPhotoType;
use App\Models\OrderPackage;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Listado para el tablero (con relaciones necesarias)
    public function index()
    {
        $orders = Order::with(['customer', 'packages', 'photoTypes'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    // Crear orden (con todo: cliente, líneas, paquetes, pago)
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Cliente: o bien customer_id (existente) o datos de nuevo cliente
            'customer_id' => 'nullable|exists:customers,id',
            'new_customer' => 'nullable|array',
            'new_customer.name' => 'required_with:new_customer|string|max:255',
            'new_customer.phone' => 'nullable|string|max:20',
            'new_customer.email' => 'nullable|email|max:255',
            'estimated_delivery_date' => 'required|date',
            'pose_number' => 'nullable|integer|min:1|max:3',
            'notes' => 'nullable|string',
            'photo_types' => 'required|array|min:1',
            'photo_types.*.photo_type_id' => 'required|exists:photo_types,id',
            'photo_types.*.quantity' => 'required|integer|min:1',
            'photo_types.*.format' => 'required|in:color,bn',
            'photo_types.*.paper_type_id' => 'nullable|exists:paper_types,id',
            'package_ids' => 'nullable|array',
            'package_ids.*' => 'exists:packages,id',
            'initial_payment' => 'nullable|array',
            'initial_payment.amount' => 'nullable|numeric|min:0',
            'initial_payment.payment_method' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            // 1) Cliente
            if ($validated['customer_id']) {
                $customer = Customer::findOrFail($validated['customer_id']);
            } else if ($validated['new_customer']) {
                $customer = Customer::create([
                    'name'  => $validated['new_customer']['name'],
                    'phone' => $validated['new_customer']['phone'] ?? null,
                    'email' => $validated['new_customer']['email'] ?? null,
                ]);
            } else {
                throw new \Exception('Debe seleccionar o crear un cliente');
            }

            // 2) Calcular total a partir de las líneas y paquetes
            $total = 0;

            // Líneas personalizadas
            $photoTypeIds = collect($validated['photo_types'])->pluck('photo_type_id');
            $photoTypes = \App\Models\PhotoType::whereIn('id', $photoTypeIds)->get()->keyBy('id');

            foreach ($validated['photo_types'] as $line) {
                $pt = $photoTypes[$line['photo_type_id']];
                $unitPrice = $pt->base_price ?? 0;
                $total += $unitPrice * $line['quantity'];
            }

            // Paquetes escolares
            if (!empty($validated['package_ids'])) {
                $packages = \App\Models\Package::whereIn('id', $validated['package_ids'])->get();
                $total += $packages->sum('total_price');
            }

            // 3) Crear la orden
            $order = Order::create([
                'customer_id'             => $customer->id,
                'total'                   => $total,
                'pose_number'             => $validated['pose_number'] ?? null,
                'notes'                   => $validated['notes'] ?? null,
                'estimated_delivery_date' => $validated['estimated_delivery_date'],
                'actual_delivery_date'    => null,
                'status'                  => 'pending', // siempre inicia pendiente
                'signature_id'            => null,
            ]);

            // 4) Guardar líneas de foto personalizadas
            foreach ($validated['photo_types'] as $line) {
                OrderPhotoType::create([
                    'order_id'      => $order->id,
                    'photo_type_id' => $line['photo_type_id'],
                    'quantity'      => $line['quantity'],
                    'unit_price'    => $photoTypes[$line['photo_type_id']]->base_price ?? 0,
                    'format'        => $line['format'],
                    'paper_type_id' => $line['paper_type_id'] ?? null,
                ]);
            }

            // 5) Guardar paquetes escolares (snapshots)
            if (!empty($validated['package_ids'])) {
                foreach ($packages as $pkg) {
                    OrderPackage::create([
                        'order_id'            => $order->id,
                        'package_id'          => $pkg->id,
                        'package_name_snapshot' => $pkg->name ?? ('Paquete ' . $pkg->package_number),
                        'price_snapshot'      => $pkg->total_price,
                    ]);
                }
            }

            // 6) Pago inicial (si se envió)
            if ($validated['initial_payment'] && $validated['initial_payment']['amount'] > 0) {
                Payment::create([
                    'order_id'       => $order->id,
                    'amount'         => $validated['initial_payment']['amount'],
                    'payment_method' => $validated['initial_payment']['payment_method'] ?? 'Efectivo',
                    'paid_at'        => now(),
                    'notes'          => 'Pago inicial',
                ]);
            }

            DB::commit();

            // Cargar relaciones para devolver la orden completa
            $order->load(['customer', 'packages', 'photoTypes']);

            return response()->json([
                'message' => 'Orden creada exitosamente',
                'order'   => $order,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear la orden: ' . $e->getMessage(),
            ], 500);
        }
    }
}