<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    protected PackageService $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function index()
    {
        return Package::with(['school:id,name', 'photoTypes:id,name,base_price'])
            ->orderBy('school_id')
            ->orderBy('package_number')
            ->get(['id', 'school_id', 'package_number', 'name', 'total_price', 'women_features', 'men_features']);
    }

    public function show(Package $package)
    {
        $package->load(['school:id,name', 'photoTypes:id,name,base_price']);
        return response()->json($package);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        try {
            $package = $this->packageService->createPackage($validated);
            return response()->json(['message' => 'Paquete creado exitosamente', 'package' => $package], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el paquete: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Package $package)
    {
        $validated = $this->validated($request, $package->id);

        try {
            $package = $this->packageService->updatePackage($package, $validated);
            return response()->json(['message' => 'Paquete actualizado exitosamente', 'package' => $package]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el paquete: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Package $package)
    {
        try {
            $this->packageService->deletePackage($package);
            return response()->json(['message' => 'Paquete eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    protected function validated(Request $request, ?int $ignorePackageId = null): array
    {
        return $request->validate([
            'school_id' => 'required|exists:schools,id',
            'package_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('packages', 'package_number')
                    ->where('school_id', $request->school_id)
                    ->ignore($ignorePackageId),
            ],
            'name' => 'nullable|string|max:255',
            'total_price' => 'required|numeric|min:0',
            'women_features_text' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if ($value !== null && str_word_count(trim($value)) > 1000) {
                    $fail('Las características de mujeres no pueden superar 1000 palabras.');
                }
            }],
            'men_features_text' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if ($value !== null && str_word_count(trim($value)) > 1000) {
                    $fail('Las características de hombres no pueden superar 1000 palabras.');
                }
            }],
            'photo_types' => 'required|array|min:1',
            'photo_types.*.photo_type_id' => 'required|exists:photo_types,id',
            'photo_types.*.quantity' => 'required|integer|min:1',
        ]);
    }
}