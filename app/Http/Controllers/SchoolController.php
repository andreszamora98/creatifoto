<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

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
}