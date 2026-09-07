<?php

namespace App\Http\Controllers;

use App\Models\PhotoType;
use Illuminate\Http\Request;

class PhotoTypeController extends Controller
{
    public function index()
    {
        return PhotoType::all(['id', 'name', 'base_price']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
        ]);

        $photoType = PhotoType::create($validated);
        return response()->json($photoType, 201);
    }

    public function update(Request $request, $id)
    {
        $photoType = PhotoType::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
        ]);
        $photoType->update($validated);
        return response()->json($photoType);
    }

    public function destroy($id)
    {
        $photoType = PhotoType::findOrFail($id);
        $photoType->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}