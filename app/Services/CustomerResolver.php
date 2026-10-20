<?php

namespace App\Services;

use App\Models\Customer;
use DomainException;
use Illuminate\Database\QueryException;

class CustomerResolver
{
    public function resolve(array $data): Customer
    {
        if (!empty($data['customer_id'])) {
            $customer = Customer::findOrFail($data['customer_id']);

            if (!empty($data['contact_update'])) {
                $customer->update(array_filter([
                    'phone' => $data['contact_update']['phone'] ?? null,
                    'email' => $data['contact_update']['email'] ?? null,
                ], fn ($value) => $value !== null));
            }

            return $customer;
        }

        if (!empty($data['new_customer'])) {
            return $this->createOrFind($data['new_customer']);
        }

        throw new DomainException('Debe seleccionar un cliente existente o crear uno nuevo.');
    }

    public function createOrFind(array $data): Customer
    {
        $rawName = trim(preg_replace('/\s+/', ' ', (string) ($data['name'] ?? '')));

        if ($rawName === '') {
            throw new DomainException('El nombre del cliente es obligatorio.');
        }

        $customer = Customer::where('name', $rawName)->lockForUpdate()->first();

        if ($customer) {
            return $customer;
        }

        try {
            return Customer::create([
                'name' => $rawName,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
            ]);
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                $customer = Customer::where('name', $rawName)->first();

                if ($customer) {
                    return $customer;
                }
            }

            throw $e;
        }
    }
}