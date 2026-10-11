<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PackageController extends Controller
{
    protected PackageService $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function index()
    {
        return Package::with([
                'school:id,name,sequence',
                'photoTypes:id,name,base_price',
                'additionalServices:id,name,default_price',
            ])
            ->orderBy('school_id')
            ->orderBy('sequence')
            ->get(['id', 'school_id', 'sequence', 'name', 'total_price', 'women_features', 'men_features']);
    }

    public function show(Package $package)
    {
        $package->load([
            'school:id,name,sequence',
            'photoTypes:id,name,base_price',
            'additionalServices:id,name,default_price',
        ]);
        return response()->json($package);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        try {
            $package = DB::transaction(function () use ($validated) {
                $validated['sequence'] = (Package::where('school_id', $validated['school_id'])
                    ->max('sequence') ?? 0) + 1;

                return $this->packageService->createPackage($validated);
            });

            return response()->json(['message' => 'Paquete creado exitosamente', 'package' => $package], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el paquete: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Package $package)
    {
        $validated = $this->validated($request, $package);

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
            DB::transaction(function () use ($package) {
                $schoolId = $package->school_id;
                $seq = $package->sequence;

                $package->delete();

                $affected = Package::where('school_id', $schoolId)
                    ->where('sequence', '>', $seq)
                    ->orderBy('sequence', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($affected as $p) {
                    $p->decrement('sequence');
                }
            });

            return response()->json(['message' => 'Paquete eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    protected function validated(Request $request, ?Package $package = null): array
    {
        $nameUnique = Rule::unique('packages', 'name')
            ->where(fn ($q) => $q
                ->whereNull('deleted_at')
                ->where('school_id', $request->input('school_id'))
            );

        if ($package) {
            $nameUnique->ignore($package->id);
        }

        $validated = $request->validate([
            'school_id' => ['required', Rule::exists('schools', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:255', $nameUnique],
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
            'photo_types.*.photo_type_id' => ['required', Rule::exists('photo_types', 'id')->whereNull('deleted_at')],
            'photo_types.*.quantity' => 'required|integer|min:1',
            'photo_types.*.format' => ['nullable', Rule::in(['color', 'bn'])],
            'photo_types.*.paper_type_id' => ['nullable', Rule::exists('paper_types', 'id')],
            'additional_services' => 'nullable|array',
            'additional_services.*.additional_service_id' => ['required', Rule::exists('additional_services', 'id')->whereNull('deleted_at')],
            'additional_services.*.quantity' => 'required|integer|min:1',
        ], [
            'name.unique' => 'Ya existe un paquete con ese nombre en esta escuela.',
            'name.required' => 'El nombre del paquete es obligatorio.',
        ]);

        $this->assertNoDuplicatePhotoLines($validated['photo_types']);

        return $validated;
    }

    protected function assertNoDuplicatePhotoLines(array $photoTypes): void
    {
        $seen = [];

        foreach ($photoTypes as $index => $line) {
            $key = implode('|', [
                (int) $line['photo_type_id'],
                $line['format'] ?? 'color',
                $line['paper_type_id'] ?? '',
            ]);

            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    "photo_types.{$index}.photo_type_id" => 'Hay líneas repetidas (mismo tipo, formato y papel). Déjalas en una sola línea.',
                ]);
            }

            $seen[$key] = true;
        }
    }
}