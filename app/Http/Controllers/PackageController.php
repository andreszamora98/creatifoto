<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        return Package::with('school:id,name')
            ->get(['id', 'school_id', 'package_number', 'name', 'total_price']);
    }
}