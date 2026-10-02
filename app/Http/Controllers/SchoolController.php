<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index()
    {
        return School::orderBy('name')->get(['id', 'name']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:schools,name',
        ]);

        $school = School::create($validated);

        return response()->json(['message' => 'Escuela creada', 'school' => $school], 201);
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('schools', 'name')->ignore($school->id)],
        ]);

        $school->update($validated);

        return response()->json(['message' => 'Escuela actualizada', 'school' => $school]);
    }

    public function destroy(School $school)
    {
        try {
            $school->delete();
            return response()->json(['message' => 'Escuela eliminada']);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'No se puede eliminar: esta escuela tiene paquetes que ya han sido vendidos en alguna orden.',
            ], 422);
        }
    }
}