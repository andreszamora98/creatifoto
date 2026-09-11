<?php

namespace App\Http\Controllers;

use App\Models\PaperType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class PaperTypeController extends Controller
{
    public function index()
    {
        return PaperType::all(['id', 'name', 'extra_price']);
    }
}