<?php

namespace App\Http\Controllers;

use App\Models\PaperType;

class PaperTypeController extends Controller
{
    public function index()
    {
        return PaperType::all(['id', 'name', 'extra_price']);
    }
}