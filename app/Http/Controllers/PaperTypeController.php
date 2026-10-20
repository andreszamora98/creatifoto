<?php

namespace App\Http\Controllers;

use App\Models\PaperType;

class PaperTypeController extends Controller
{
    public function index()
    {
        return PaperType::orderBy('name')->get(['id', 'name', 'extra_price']);
    }
}