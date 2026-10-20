<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDomainErrors;
use App\Models\PhotoType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhotoTypeController extends Controller
{
    use HandlesDomainErrors;

    public function index()
    {
        return PhotoType::orderBy('name')->get(['id', 'name', 'base_price', 'base_quantity']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('photo_types', 'name')->whereNull('deleted_at')],
            'base_price' => 'required|numeric|min:0|max:9999999.99',
            'base_quantity' => 'required|integer|min:1|max:1000',
        ]);

        return $this->respondDomain(
            fn () => response()->json(PhotoType::create($validated), 201),
            null,
            201,
            'Error al crear el tipo de fotografía. Inténtalo de nuevo.'
        );
    }

    public function update(Request $request, $id)
    {
        $photoType = PhotoType::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('photo_types', 'name')->whereNull('deleted_at')->ignore($photoType->id),
            ],
            'base_price' => 'required|numeric|min:0|max:9999999.99',
            'base_quantity' => 'required|integer|min:1|max:1000',
        ]);

        return $this->respondDomain(function () use ($photoType, $validated) {
            $photoType->update($validated);

            return response()->json($photoType);
        }, null, 200, 'Error al actualizar el tipo de fotografía. Inténtalo de nuevo.');
    }

    public function destroy($id)
    {
        $photoType = PhotoType::findOrFail($id);

        return $this->respondDomain(function () use ($photoType) {
            if ($photoType->packages()->exists()) {
                throw new \DomainException(
                    'No se puede eliminar: este tipo de fotografía forma parte de uno o más paquetes. Quítalo de esos paquetes primero.'
                );
            }

            $photoType->delete();

            return ['message' => 'Eliminado'];
        }, null, 200, 'Error al eliminar el tipo de fotografía. Inténtalo de nuevo.');
    }
}