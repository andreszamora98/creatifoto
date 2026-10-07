<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        return Order::with([
            'customer:id,name,phone,email',
            'packages:id,sequence,name,total_price',
            'orderPhotoTypes.photoType:id,name,base_price',
            'orderPhotoTypes.paperType:id,name,extra_price',
            'payments',
            'additionalServices'
        ])
        ->orderBy('created_at', 'desc')
        ->get(['id', 'customer_id', 'total', 'balance_due', 'pose_number', 'notes',
               'estimated_delivery_date', 'estimated_delivery_time', 'actual_delivery_date', 'status',
               'signature_id', 'created_at']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'new_customer' => 'nullable|array',
            'new_customer.name' => 'required_with:new_customer|string|max:255',
            'new_customer.phone' => 'nullable|digits:10',
            'new_customer.email' => 'nullable|email|max:255|unique:customers,email',

            'contact_update.phone' => 'nullable|digits:10',
            'contact_update.email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('customers', 'email')->ignore($request->customer_id),
            ],

            'estimated_delivery_date' => 'required|date|after_or_equal:today',
            'estimated_delivery_time' => 'nullable|date_format:H:i',
            'pose_number' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:1000',

            'photo_types' => 'nullable|array|required_without:package_ids',
            'photo_types.*.photo_type_id' => 'required|exists:photo_types,id',
            'photo_types.*.quantity' => 'required|integer|min:1',
            'photo_types.*.format' => ['required', Rule::in(['color', 'bn'])],
            'photo_types.*.paper_type_id' => 'nullable|exists:paper_types,id',
            'package_ids' => 'nullable|array|required_without:photo_types',
            'package_ids.*' => 'exists:packages,id',

            'additional_services' => 'nullable|array',
            'additional_services.*.additional_service_id' => 'required|exists:additional_services,id|distinct',
            'additional_services.*.price' => 'required|numeric|min:0',
            'additional_services.*.quantity' => 'required|integer|min:1',
            'additional_services.*.notes' => 'nullable|string|max:255',

            'payments' => 'required|array|min:1',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.payment_method' => 'nullable|string|max:50',
        ], [
            'photo_types.required_without' => 'Agrega al menos un tipo de fotografía o un paquete escolar.',
            'package_ids.required_without' => 'Agrega al menos un tipo de fotografía o un paquete escolar.',
        ]);

        try {
            $order = $this->orderService->createOrder($validated);
            return response()->json([
                'message' => 'Orden creada exitosamente',
                'order' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la orden: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function addPayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'type' => ['required', Rule::in(['advance', 'settlement'])],
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $result = $this->orderService->addPayment($order, $validated);
            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function deliver(Order $order)
    {
        try {
            $result = $this->orderService->markAsDelivered($order);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Order $order)
    {
        $order->load([
            'customer:id,name,phone,email',
            'packages:id,sequence,name,total_price',
            'orderPhotoTypes.photoType:id,name,base_price',
            'orderPhotoTypes.paperType:id,name,extra_price',
            'payments',
            'additionalServices',
        ]);

        return response()->json($order);
    }
}