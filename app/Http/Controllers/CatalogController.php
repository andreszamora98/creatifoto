<?php

namespace App\Http\Controllers;

use App\Models\PhotoType;
use App\Models\PaperType;
use App\Models\Package;
use App\Models\Customer;

class CatalogController extends Controller
{
    public function photoTypes()
    {
        return response()->json(PhotoType::all());
    }

    public function paperTypes()
    {
        return response()->json(PaperType::all());
    }

    public function packages()
    {
        return response()->json(Package::with('school')->get());
    }

    public function customers()
    {
        return response()->json(Customer::all());
    }
}
