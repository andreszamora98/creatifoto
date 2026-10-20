<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDomainErrors;
use App\Models\AdditionalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdditionalServiceController extends Controller
{
    use HandlesDomainErrors;

    public function index()
    {
        return AdditionalService::orderBy('name')->get(['id', 'name', 'default_price', 'default_quantity']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('additional_services', 'name')->whereNull('deleted_at')],
            'default_price' => 'required|numeric|min:0|max:9999999.99',
            'default_quantity' => 'required|integer|min:1|max:1000',
        ]);

        return $this->respondDomain(
            fn () => response()->json(AdditionalService::create($validated), 201),
            null,
            201,
            'Error al crear el servicio adicional. Inténtalo de nuevo.'
        );
    }

    public function update(Request $request, $id)
    {
        $service = AdditionalService::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('additional_services', 'name')->whereNull('deleted_at')->ignore($service->id),
            ],
            'default_price' => 'required|numeric|min:0|max:9999999.99',
            'default_quantity' => 'required|integer|min:1|max:1000',
        ]);

        return $this->respondDomain(function () use ($service, $validated) {
            $service->update($validated);

            return response()->json($service);
        }, null, 200, 'Error al actualizar el servicio adicional. Inténtalo de nuevo.');
    }

    public function destroy($id)
    {
        $service = AdditionalService::findOrFail($id);

        return $this->respondDomain(function () use ($service) {
            if ($service->packages()->exists()) {
                throw new \DomainException(
                    'No se puede eliminar: este servicio adicional forma parte de uno o más paquetes. Quítalo de esos paquetes primero.'
                );
            }

            $service->delete();

            return ['message' => 'Eliminado'];
        }, null, 200, 'Error al eliminar el servicio adicional. Inténtalo de nuevo.');
    }
}