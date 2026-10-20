<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDomainErrors;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    use HandlesDomainErrors;

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
            'email' => [
                'sometimes', 'nullable', 'email', 'max:255',
                Rule::unique('customers', 'email')->ignore($customer->id),
            ],
        ]);

        return $this->respondDomain(
            fn () => ['customer' => tap($customer)->update($validated)],
            null,
            200,
            'Error al actualizar los datos de contacto. Inténtalo de nuevo.'
        );
    }
}