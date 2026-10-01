<?php

namespace App\Http\Controllers;

use App\Models\PhotoType;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhotoTypeController extends Controller
{
    public function index()
    {
        return PhotoType::orderBy('name')->get(['id', 'name', 'base_price', 'base_quantity']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:photo_types,name',
            'base_price' => 'required|numeric|min:0',
            'base_quantity' => 'required|integer|min:1',
        ]);

        $photoType = PhotoType::create($validated);
        return response()->json($photoType, 201);
    }

    public function update(Request $request, $id)
    {
        $photoType = PhotoType::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('photo_types', 'name')->ignore($photoType->id)],
            'base_price' => 'required|numeric|min:0',
            'base_quantity' => 'required|integer|min:1',
        ]);

        $photoType->update($validated);
        return response()->json($photoType);
    }

    public function destroy($id)
    {
        $photoType = PhotoType::findOrFail($id);

        try {
            $photoType->delete();
            return response()->json(['message' => 'Eliminado']);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'No se puede eliminar: este tipo de fotografía ya está en uso en una o más órdenes, cotizaciones o paquetes.',
            ], 422);
        }
    }
}