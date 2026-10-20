<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDomainErrors;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    use HandlesDomainErrors;

    public function index()
    {
        return School::orderBy('sequence')->get(['id', 'name', 'sequence']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('schools', 'name')->whereNull('deleted_at')],
        ]);

        return $this->respondDomain(function () use ($validated) {
            $school = DB::transaction(function () use ($validated) {
                $last = School::orderByDesc('sequence')->lockForUpdate()->first();
                $validated['sequence'] = ($last?->sequence ?? 0) + 1;

                return School::create($validated);
            });

            return ['school' => $school];
        }, 'Escuela creada', 201, 'Error al crear la escuela. Inténtalo de nuevo.');
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('schools', 'name')->whereNull('deleted_at')->ignore($school->id),
            ],
        ]);

        return $this->respondDomain(function () use ($school, $validated) {
            $school->update($validated);

            return ['school' => $school];
        }, 'Escuela actualizada', 200, 'Error al actualizar la escuela. Inténtalo de nuevo.');
    }

    public function destroy(School $school)
    {
        return $this->respondDomain(function () use ($school) {
            DB::transaction(function () use ($school) {
                $school = School::withTrashed()->lockForUpdate()->find($school->id);

                if (!$school || $school->trashed()) {
                    throw new \DomainException('Esta escuela ya fue eliminada.');
                }

                $seq = $school->sequence;

                $school->packages()->get()->each->delete();
                $school->delete();

                $affected = School::where('sequence', '>', $seq)
                    ->orderBy('sequence', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($affected as $s) {
                    $s->decrement('sequence');
                }
            });

            return ['message' => 'Escuela eliminada'];
        }, null, 200, 'Error al eliminar la escuela. Inténtalo de nuevo.');
    }
}