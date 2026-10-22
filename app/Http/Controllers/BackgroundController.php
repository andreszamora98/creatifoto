<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDomainErrors;
use App\Models\Background;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BackgroundController extends Controller
{
    use HandlesDomainErrors;

    public function index(Request $request)
    {
        $query = Background::orderBy('name');

        if (!$request->boolean('include_inactive')) {
            $query->where('is_active', true);
        }

        return $query->get(['id', 'name', 'is_active']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('backgrounds', 'name')->whereNull('deleted_at')],
            'is_active' => 'sometimes|boolean',
        ]);

        return $this->respondDomain(
            fn () => ['background' => Background::create([
                'name' => trim($validated['name']),
                'is_active' => $validated['is_active'] ?? true,
            ])],
            'Fondo creado',
            201,
            'Error al crear el fondo. Inténtalo de nuevo.'
        );
    }

    public function update(Request $request, Background $background)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('backgrounds', 'name')->whereNull('deleted_at')->ignore($background->id),
            ],
            'is_active' => 'sometimes|boolean',
        ]);

        return $this->respondDomain(function () use ($background, $validated) {
            $background->name = trim($validated['name']);
            if (array_key_exists('is_active', $validated)) {
                $background->is_active = (bool) $validated['is_active'];
            }
            $background->save();

            return ['background' => $background];
        }, 'Fondo actualizado', 200, 'Error al actualizar el fondo. Inténtalo de nuevo.');
    }
}