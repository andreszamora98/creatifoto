<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::select('id', 'name', 'phone', 'email')
            ->orderBy('name')
            ->get();
    }

    public function updateContact(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'phone' => 'sometimes|nullable|digits:10',
            'email' => 'sometimes|nullable|email|max:255|unique:customers,email,' . $customer->id,
        ]);
        $customer->update($validated);
        return response()->json($customer);
    }
}