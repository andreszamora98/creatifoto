<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDomainErrors;
use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    use HandlesDomainErrors;

    public function __construct(protected QuoteService $quoteService)
    {
    }

    public function index(Request $request)
    {
        $query = Quote::with(array_merge(QuoteService::RELATIONS, ['order:id,quote_id,status']))
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('page') || $request->filled('per_page')) {
            $perPage = (int) min(max($request->integer('per_page', 25), 1), 100);

            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->contentRules($request) + [
            'valid_until' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'discount_type' => 'nullable|required_with:discount_value|in:fixed,percent',
            'discount_value' => $this->discountValueRules($request),
            'discount_reason' => 'nullable|string|max:255',
        ], $this->contentMessages());

        return $this->respondDomain(
            fn () => ['quote' => $this->quoteService->createQuote($validated)],
            'Cotización creada exitosamente',
            201,
            'Error al crear la cotización. Inténtalo de nuevo.'
        );
    }

    public function show(Quote $quote)
    {
        $quote->load(array_merge(QuoteService::RELATIONS, ['order:id,quote_id,status,total,balance_due']));

        return response()->json($quote);
    }

    public function convert(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'estimated_delivery_date' => 'required|date|after_or_equal:today',
            'estimated_delivery_time' => 'required|date_format:H:i',
            'pose_number' => [
                'nullable', 'integer', 'min:1',
                Rule::unique('orders', 'pose_number')->whereNull('deleted_at'),
            ],
            'notes' => 'nullable|string|max:1000',
            'discount_type' => 'nullable|in:fixed,percent',
            'discount_value' => $this->discountValueRules($request),
            'discount_reason' => 'nullable|string|max:255',
            'payments' => 'required|array|min:1|max:20',
            'payments.*.amount' => 'required|numeric|min:0.01|max:9999999.99',
            'payments.*.payment_method' => 'nullable|string|max:50',
        ], [
            'pose_number.unique' => 'Ya existe una orden activa con ese número de toma.',
        ]);

        return $this->respondDomain(
            fn () => ['order' => $this->quoteService->convertToOrder($quote, $validated)],
            'Orden creada a partir de la cotización',
            201,
            'Error al convertir la cotización. Inténtalo de nuevo.'
        );
    }

    public function updateStatus(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'rejected', 'expired'])],
        ]);

        return $this->respondDomain(
            fn () => ['quote' => $this->quoteService->updateStatus($quote, $validated['status'])],
            'Estado actualizado',
            200,
            'Error al actualizar el estado. Inténtalo de nuevo.'
        );
    }

    public function patch(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'valid_until' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        return $this->respondDomain(
            fn () => ['quote' => $this->quoteService->patchQuote($quote, $validated)],
            'Cotización actualizada',
            200,
            'Error al actualizar la cotización. Inténtalo de nuevo.'
        );
    }

    public function updateConcepts(Request $request, Quote $quote)
    {
        $validated = $request->validate($this->contentRules($request) + [
            'discount_type' => 'nullable|required_with:discount_value|in:fixed,percent',
            'discount_value' => $this->discountValueRules($request),
            'discount_reason' => 'nullable|string|max:255',
        ], $this->contentMessages());

        return $this->respondDomain(
            fn () => ['quote' => $this->quoteService->updateConcepts($quote, $validated)],
            'Conceptos actualizados',
            200,
            'Error al actualizar los conceptos. Inténtalo de nuevo.'
        );
    }

    public function updateCustomer(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'new_customer' => 'nullable|array',
            'new_customer.name' => 'required_with:new_customer|string|max:255',
            'new_customer.phone' => 'nullable|digits:10',
            'new_customer.email' => 'nullable|email|max:255|unique:customers,email',
        ]);

        return $this->respondDomain(
            fn () => ['quote' => $this->quoteService->updateCustomer($quote, $validated)],
            'Cliente actualizado',
            200,
            'Error al actualizar el cliente. Inténtalo de nuevo.'
        );
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

    private function contentMessages(): array
    {
        $message = 'Agrega al menos un tipo de fotografía o un paquete escolar.';

        return [
            'photo_types.required_without' => $message,
            'package_ids.required_without' => $message,
        ];
    }

    private function discountValueRules(Request $request): array
    {
        return [
            'nullable', 'numeric', 'min:0', 'required_with:discount_type',
            Rule::when(
                fn () => $request->input('discount_type') === 'percent',
                ['max:100']
            ),
        ];
    }
}