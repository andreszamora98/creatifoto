<?php

namespace App\Http\Controllers;

use App\Models\AdditionalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdditionalServiceController extends Controller
{
    public function index()
    {
        return AdditionalService::orderBy('name')->get(['id', 'name', 'default_price', 'default_quantity']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('additional_services', 'name')->whereNull('deleted_at')],
            'default_price' => 'required|numeric|min:0',
            'default_quantity' => 'required|integer|min:1',
        ]);

        $service = AdditionalService::create($validated);

        return response()->json($service, 201);
    }

    public function update(Request $request, $id)
    {
        $service = AdditionalService::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('additional_services', 'name')->whereNull('deleted_at')->ignore($service->id),
            ],
            'default_price' => 'required|numeric|min:0',
            'default_quantity' => 'required|integer|min:1',
        ]);

        $service->update($validated);

        return response()->json($service);
    }

    /** Soft-delete: las órdenes y cotizaciones conservan el nombre y precio de la línea. */
    public function destroy($id)
    {
        AdditionalService::findOrFail($id)->delete();

        return response()->json(['message' => 'Eliminado']);
    }
}