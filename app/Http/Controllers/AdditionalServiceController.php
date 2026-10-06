<?php

namespace App\Http\Controllers;

use App\Models\AdditionalService;
use Illuminate\Database\QueryException;
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
            'name' => 'required|string|max:255|unique:additional_services,name',
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
            'name' => ['required', 'string', 'max:255', Rule::unique('additional_services', 'name')->ignore($service->id)],
            'default_price' => 'required|numeric|min:0',
            'default_quantity' => 'required|integer|min:1',
        ]);

        $service->update($validated);

        return response()->json($service);
    }

    public function destroy($id)
    {
        $service = AdditionalService::findOrFail($id);

        try {
            $service->delete();
            return response()->json(['message' => 'Eliminado']);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'No se puede eliminar: este servicio adicional ya está en uso en una o más órdenes.',
            ], 422);
        }
    }
}