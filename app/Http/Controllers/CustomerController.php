<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::select('id', 'name', 'phone', 'email')
            ->orderBy('name')
            ->get();
    }
}