<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDomainErrors;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    use HandlesDomainErrors;

    public function __construct(protected OrderService $orderService)
    {
    }

    public function index(Request $request)
    {
        $query = Order::with(OrderService::RELATIONS)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $columns = [
            'id', 'customer_id', 'total', 'initial_total', 'balance_due',
            'discount_type', 'discount_value', 'discount_amount', 'discount_reason',
            'pose_number', 'archive_name', 'pose_range', 'notes',
            'estimated_delivery_date', 'estimated_delivery_time', 'actual_delivery_date', 'status',
            'signature_id', 'created_at',
        ];

        if ($request->filled('page') || $request->filled('per_page')) {
            $perPage = (int) min(max($request->integer('per_page', 25), 1), 100);

            return $query->paginate($perPage, $columns);
        }

        return $query->get($columns);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->contentRules($request) + [
            'contact_update.phone' => 'nullable|digits:10',
            'contact_update.email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('customers', 'email')->ignore($request->customer_id),
            ],

            'estimated_delivery_date' => 'required|date|after_or_equal:today',
            'estimated_delivery_time' => 'required|date_format:H:i',
            'pose_number' => [
                'nullable', 'integer', 'min:1',
                Rule::unique('orders', 'pose_number')->whereNull('deleted_at'),
            ],
            'notes' => 'nullable|string|max:1000',

            'discount_type' => 'nullable|in:fixed,percent',
            'discount_source' => 'nullable|in:auto,manual,none',
            'discount_applies_to_packages' => 'nullable|boolean',
            'discount_value' => $this->discountValueRules($request),
            'discount_reason' => 'nullable|string|max:255',

            'pickup_persons' => 'nullable|array|max:20',
            'pickup_persons.*.customer_id' => 'nullable|integer|exists:customers,id',
            'pickup_persons.*.new_customer' => 'nullable|array',
            'pickup_persons.*.new_customer.name' => 'required_with:pickup_persons.*.new_customer|nullable|string|max:255',
            'pickup_persons.*.new_customer.phone' => 'nullable|digits:10',
            'pickup_persons.*.new_customer.email' => 'nullable|email|max:255',
            'pickup_persons.*.relationship' => 'nullable|string|max:50',

            'payments' => 'required|array|min:1',
            'payments.*.amount' => 'required|numeric|min:0.01|max:9999999.99',
            'payments.*.payment_method' => 'nullable|string|max:50',
        ], [
            'photo_types.required_without' => 'Agrega al menos un tipo de fotografía o un paquete escolar.',
            'package_ids.required_without' => 'Agrega al menos un tipo de fotografía o un paquete escolar.',
            'pose_number.unique' => 'Ya existe una orden activa con ese número de toma.',
            'estimated_delivery_time.required' => 'La hora de entrega es obligatoria.',
        ]);

        return $this->respondDomain(
            fn () => ['order' => $this->orderService->createOrder($validated)],
            'Orden creada exitosamente',
            201,
            'Error al crear la orden. Inténtalo de nuevo.'
        );
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate($this->contentRules($request) + [
            'estimated_delivery_date' => 'required|date',
            'estimated_delivery_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:1000',

            'discount_type' => 'nullable|in:fixed,percent',
            'discount_source' => 'nullable|in:auto,manual,none',
            'discount_applies_to_packages' => 'nullable|boolean',
            'discount_value' => $this->discountValueRules($request),
            'discount_reason' => 'nullable|string|max:255',
        ], [
            'photo_types.required_without' => 'Agrega al menos un tipo de fotografía o un paquete escolar.',
            'package_ids.required_without' => 'Agrega al menos un tipo de fotografía o un paquete escolar.',
        ]);

        return $this->respondDomain(
            fn () => ['order' => $this->orderService->updateOrder($order, $validated)],
            'Orden actualizada',
            200,
            'Error al actualizar la orden. Inténtalo de nuevo.'
        );
    }

    public function updateMetadata(Request $request, Order $order)
    {
        $validated = $request->validate([
            'pose_number' => [
                'nullable', 'integer', 'min:1',
                Rule::unique('orders', 'pose_number')
                    ->whereNull('deleted_at')
                    ->ignore($order->id),
            ],
            'archive_name' => 'nullable|string|max:255',
            'pose_range' => 'nullable|string|max:255',
        ], [
            'pose_number.unique' => 'Ya existe una orden activa con ese número de toma.',
        ]);

        return $this->respondDomain(
            fn () => ['order' => $this->orderService->updateMetadata($order, $validated)],
            'Datos actualizados',
            200,
            'Error al actualizar los datos. Inténtalo de nuevo.'
        );
    }

    public function addPayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
            'discount_source' => 'nullable|in:auto,manual,none',
            'discount_applies_to_packages' => 'nullable|boolean',
        ]);

        return $this->respondDomain(
            fn () => $this->orderService->addPayment($order, $validated),
            null,
            201,
            'Error al registrar el pago. Inténtalo de nuevo.'
        );
    }

    public function deliver(Order $order)
    {
        return $this->respondDomain(
            fn () => $this->orderService->markAsDelivered($order),
            null,
            200,
            'Error al entregar la orden. Inténtalo de nuevo.'
        );
    }

    public function syncPickups(Request $request, Order $order)
    {
        $validated = $request->validate([
            'pickup_persons' => 'present|array|max:20',
            'pickup_persons.*.customer_id' => 'nullable|integer|exists:customers,id',
            'pickup_persons.*.new_customer' => 'nullable|array',
            'pickup_persons.*.new_customer.name' => 'required_with:pickup_persons.*.new_customer|nullable|string|max:255',
            'pickup_persons.*.new_customer.phone' => 'nullable|digits:10',
            'pickup_persons.*.new_customer.email' => 'nullable|email|max:255',
            'pickup_persons.*.relationship' => 'nullable|string|max:50',
        ]);

        return $this->respondDomain(
            fn () => ['order' => $this->orderService->syncOrderPickups($order, $validated['pickup_persons'])],
            'Personas autorizadas actualizadas',
            200,
            'Error al actualizar las personas autorizadas. Inténtalo de nuevo.'
        );
    }

    public function show(Order $order)
    {
        $order->load(OrderService::RELATIONS);

        return response()->json($order);
    }

    private function contentRules(Request $request): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'new_customer' => 'nullable|array',
            'new_customer.name' => 'required_with:new_customer|string|max:255',
            'new_customer.phone' => 'nullable|digits:10',
            'new_customer.email' => 'nullable|email|max:255|unique:customers,email',

            'photo_types' => 'nullable|array|required_without:package_ids|max:50',
            'photo_types.*.id' => 'nullable|integer',
            'photo_types.*.photo_type_id' => 'required|exists:photo_types,id',
            'photo_types.*.quantity' => 'required|integer|min:1|max:1000',
            'photo_types.*.format' => ['required', Rule::in(['color', 'bn'])],
            'photo_types.*.background' => 'nullable|string|max:100',
            'photo_types.*.paper_type_id' => 'nullable|exists:paper_types,id',

            'package_ids' => 'nullable|array|required_without:photo_types|max:20',
            'package_ids.*' => 'exists:packages,id',

            'additional_services' => 'nullable|array|max:50',
            'additional_services.*.additional_service_id' => 'required|exists:additional_services,id|distinct',
            'additional_services.*.price' => 'required|numeric|min:0|max:9999999.99',
            'additional_services.*.quantity' => 'required|integer|min:1|max:1000',
            'additional_services.*.notes' => 'nullable|string|max:255',
        ];
    }

    private function discountValueRules(Request $request): array
    {
        return [
            'nullable',
            'numeric',
            'min:0',
            'required_with:discount_type',
            Rule::when(
                fn () => $request->input('discount_type') === 'percent',
                ['max:100']
            ),
        ];
    }
}