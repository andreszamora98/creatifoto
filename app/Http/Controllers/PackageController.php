<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDomainErrors;
use App\Models\Package;
use App\Models\School;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PackageController extends Controller
{
    use HandlesDomainErrors;

    public function __construct(protected PackageService $packageService)
    {
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
            ->get([
                'id', 'school_id', 'sequence', 'name',
                'total_price', 'list_price', 'discount_amount',
                'discount_type', 'discount_value', 'discount_source',
                'discount_reason', 'auto_discount_reason',
                'women_features', 'men_features', 'package_notes',
            ]);
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

        return $this->respondDomain(function () use ($validated) {
            $package = DB::transaction(function () use ($validated) {
                School::lockForUpdate()->find($validated['school_id']);

                $validated['sequence'] = (Package::where('school_id', $validated['school_id'])
                    ->max('sequence') ?? 0) + 1;

                return $this->packageService->createPackage($validated);
            });

            return ['package' => $package];
        }, 'Paquete creado exitosamente', 201, 'Error al crear el paquete. Inténtalo de nuevo.');
    }

    public function update(Request $request, Package $package)
    {
        $validated = $this->validated($request, $package);

        return $this->respondDomain(function () use ($package, $validated) {
            return ['package' => $this->packageService->updatePackage($package, $validated)];
        }, 'Paquete actualizado exitosamente', 200, 'Error al actualizar el paquete. Inténtalo de nuevo.');
    }

    public function destroy(Package $package)
    {
        return $this->respondDomain(function () use ($package) {
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

            return [];
        }, 'Paquete eliminado correctamente', 200, 'Error al eliminar el paquete. Inténtalo de nuevo.');
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
            'list_price' => 'nullable|numeric|min:0|max:9999999.99',
            'total_price' => 'nullable|numeric|min:0|max:9999999.99',
            'discount_source' => 'nullable|in:auto,manual,none',
            'discount_type' => 'nullable|in:percent,fixed,amount',
            'discount_value' => 'nullable|numeric|min:0|max:9999999.99',
            'discount_reason' => 'nullable|string|max:255',
            'package_notes' => 'nullable|string|max:2000',
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
            'photo_types' => 'required|array|min:1|max:50',
            'photo_types.*.photo_type_id' => ['required', Rule::exists('photo_types', 'id')->whereNull('deleted_at')],
            'photo_types.*.quantity' => 'required|integer|min:1|max:1000',
            'photo_types.*.format' => ['nullable', Rule::in(['color', 'bn'])],
            'photo_types.*.background' => 'nullable|string|max:100',
            'photo_types.*.paper_type_id' => ['nullable', Rule::exists('paper_types', 'id')],
            'additional_services' => 'nullable|array|max:50',
            'additional_services.*.additional_service_id' => ['required', Rule::exists('additional_services', 'id')->whereNull('deleted_at')],
            'additional_services.*.quantity' => 'required|integer|min:1|max:1000',
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
                $line['background'] ?? 'blanco',
                $line['paper_type_id'] ?? '',
            ]);

            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    "photo_types.{$index}.photo_type_id" => 'Hay líneas repetidas (mismo tipo, formato, fondo y papel). Déjalas en una sola línea.',
                ]);
            }

            $seen[$key] = true;
        }
    }
}