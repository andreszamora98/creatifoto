<?php

namespace App\Http\Controllers;

use App\Models\Background;

class BackgroundController extends Controller
{
    public function index()
    {
        return Background::orderBy('id')->get(['id', 'name']);
    }
}