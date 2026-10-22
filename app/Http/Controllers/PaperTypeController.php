<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDomainErrors;
use App\Models\PaperType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaperTypeController extends Controller
{
    use HandlesDomainErrors;

    public function index(Request $request)
    {
        $query = PaperType::orderBy('name');

        if (!$request->boolean('include_inactive')) {
            $query->where('is_active', true);
        }

        return $query->get(['id', 'name', 'extra_price', 'is_active']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('paper_types', 'name')->whereNull('deleted_at')],
            'extra_price' => 'nullable|numeric|min:0|max:9999999.99',
            'is_active' => 'sometimes|boolean',
        ]);

        return $this->respondDomain(
            fn () => ['paper_type' => PaperType::create([
                'name' => trim($validated['name']),
                'extra_price' => $validated['extra_price'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ])],
            'Tipo de papel creado',
            201,
            'Error al crear el tipo de papel. Inténtalo de nuevo.'
        );
    }

    public function update(Request $request, PaperType $paperType)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('paper_types', 'name')->whereNull('deleted_at')->ignore($paperType->id),
            ],
            'extra_price' => 'nullable|numeric|min:0|max:9999999.99',
            'is_active' => 'sometimes|boolean',
        ]);

        return $this->respondDomain(function () use ($paperType, $validated) {
            $paperType->name = trim($validated['name']);
            if (array_key_exists('extra_price', $validated)) {
                $paperType->extra_price = $validated['extra_price'];
            }
            if (array_key_exists('is_active', $validated)) {
                $paperType->is_active = (bool) $validated['is_active'];
            }
            $paperType->save();

            return ['paper_type' => $paperType];
        }, 'Tipo de papel actualizado', 200, 'Error al actualizar el tipo de papel. Inténtalo de nuevo.');
    }
}