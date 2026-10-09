<?php

namespace App\Http\Controllers;

use App\Models\PhotoType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhotoTypeController extends Controller
{
    /** Solo activos (el global scope de SoftDeletes excluye los eliminados). */
    public function index()
    {
        return PhotoType::orderBy('name')->get(['id', 'name', 'base_price', 'base_quantity']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('photo_types', 'name')->whereNull('deleted_at')],
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
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('photo_types', 'name')->whereNull('deleted_at')->ignore($photoType->id),
            ],
            'base_price' => 'required|numeric|min:0',
            'base_quantity' => 'required|integer|min:1',
        ]);

        $photoType->update($validated);
        return response()->json($photoType);
    }

    public function destroy($id)
    {
        $photoType = PhotoType::findOrFail($id);

        if ($photoType->packages()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: este tipo de fotografía forma parte de uno o más paquetes. Quítalo de esos paquetes primero.',
            ], 422);
        }

        $photoType->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}