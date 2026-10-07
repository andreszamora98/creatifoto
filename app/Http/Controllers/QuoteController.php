<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    protected QuoteService $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    public function index(Request $request)
    {
        $query = Quote::with([
            'customer:id,name,phone,email',
            'packages:id,sequence,name,total_price',
            'quotePhotoTypes.photoType:id,name,base_price',
            'order:id,quote_id,status',
            'additionalServices',
        ])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge([
            'customer_id' => 'nullable|exists:customers,id',
            'new_customer' => 'nullable|array',
            'new_customer.name' => 'required_with:new_customer|string|max:255',
            'new_customer.phone' => 'nullable|digits:10',
            'new_customer.email' => 'nullable|email|max:255|unique:customers,email',

            'valid_until' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',

            'photo_types' => 'nullable|array|required_without:package_ids',
            'photo_types.*.photo_type_id' => 'required|exists:photo_types,id',
            'photo_types.*.quantity' => 'required|integer|min:1',
            'photo_types.*.format' => ['required', Rule::in(['color', 'bn'])],
            'photo_types.*.paper_type_id' => 'nullable|exists:paper_types,id',
            'package_ids' => 'nullable|array|required_without:photo_types',
            'package_ids.*' => 'exists:packages,id',
        ], $this->serviceRules()), $this->contentMessages());

        try {
            $quote = $this->quoteService->createQuote($validated);
            return response()->json([
                'message' => 'Cotización creada exitosamente',
                'quote' => $quote,
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'message' => 'Error al crear la cotización. Inténtalo de nuevo.',
            ], 500);
        }
    }

    public function show(Quote $quote)
    {
        $quote->load([
            'customer:id,name,phone,email',
            'packages:id,sequence,name,total_price',
            'quotePhotoTypes.photoType:id,name,base_price',
            'quotePhotoTypes.paperType:id,name,extra_price',
            'order:id,quote_id,status,total,balance_due',
            'additionalServices',
        ]);

        return response()->json($quote);
    }

    public function convert(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'estimated_delivery_date' => 'required|date|after_or_equal:today',
            'estimated_delivery_time' => 'required|date_format:H:i',
            'pose_number' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'photo_types' => 'nullable|array',
            'photo_types.*.photo_type_id' => 'required_with:photo_types|exists:photo_types,id',
            'photo_types.*.quantity' => 'required_with:photo_types|integer|min:1',
            'photo_types.*.format' => ['required_with:photo_types', Rule::in(['color', 'bn'])],
            'photo_types.*.paper_type_id' => 'nullable|exists:paper_types,id',
            'package_ids' => 'nullable|array',
            'package_ids.*' => 'exists:packages,id',
            'payments' => 'required|array|min:1',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.payment_method' => 'nullable|string|max:50',
        ]);

        try {
            $order = $this->quoteService->convertToOrder($quote, $validated);
            return response()->json([
                'message' => 'Orden creada a partir de la cotización',
                'order' => $order,
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'message' => 'Error al convertir la cotización. Inténtalo de nuevo.',
            ], 500);
        }
    }

    public function updateStatus(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'rejected', 'expired'])],
        ]);

        try {
            $quote = $this->quoteService->updateStatus($quote, $validated['status']);
            return response()->json(['message' => 'Estado actualizado', 'quote' => $quote]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function patch(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'valid_until' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $quote = $this->quoteService->patchQuote($quote, $validated);
            return response()->json(['message' => 'Cotización actualizada', 'quote' => $quote]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function updateConcepts(Request $request, Quote $quote)
    {
        $validated = $request->validate(array_merge([
            'photo_types' => 'nullable|array|required_without:package_ids',
            'photo_types.*.photo_type_id' => 'required|exists:photo_types,id',
            'photo_types.*.quantity' => 'required|integer|min:1',
            'photo_types.*.format' => ['required', Rule::in(['color', 'bn'])],
            'photo_types.*.paper_type_id' => 'nullable|exists:paper_types,id',
            'package_ids' => 'nullable|array|required_without:photo_types',
            'package_ids.*' => 'exists:packages,id',
        ], $this->serviceRules()), $this->contentMessages());

        try {
            $quote = $this->quoteService->updateConcepts($quote, $validated);
            return response()->json(['message' => 'Conceptos actualizados', 'quote' => $quote]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'message' => 'Error al actualizar los conceptos. Inténtalo de nuevo.',
            ], 500);
        }
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

        try {
            $quote = $this->quoteService->updateCustomer($quote, $validated);
            return response()->json([
                'message' => 'Cliente actualizado',
                'quote' => $quote,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function contentMessages(): array
    {
        $message = 'Agrega al menos un tipo de fotografía o un paquete escolar.';

        return [
            'photo_types.required_without' => $message,
            'package_ids.required_without' => $message,
        ];
    }

    private function serviceRules(): array
    {
        return [
            'additional_services' => 'nullable|array',
            'additional_services.*.additional_service_id' => 'required|exists:additional_services,id|distinct',
            'additional_services.*.price' => 'required|numeric|min:0',
            'additional_services.*.quantity' => 'required|integer|min:1',
            'additional_services.*.notes' => 'nullable|string|max:255',
        ];
    }
}