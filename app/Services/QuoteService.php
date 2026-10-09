<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public const RELATIONS = [
        'customer:id,name,phone,email',
        'quotePackages',
        'quotePhotoTypes',
        'services',
    ];

    public function __construct(
        protected ContentSnapshotService $content,
        protected OrderService $orders
    ) {
    }

    public function createQuote(array $data)
    {
        return DB::transaction(function () use ($data) {
            $this->ensureHasContent($data['photo_types'] ?? [], $data['package_ids'] ?? []);

            $customer = $this->resolveCustomer($data);

            $quote = Quote::create([
                'customer_id' => $customer->id,
                'total' => 0,
                'notes' => $data['notes'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'status' => 'pending',
            ]);

            $this->content->createFor($quote, $data);
            $quote->update(['total' => $this->content->recalculate($quote)]);

            return $quote->load(self::RELATIONS);
        });
    }

    /**
     * La orden se crea a partir del estado histórico de la cotización (sin consultar catálogo).
     * La cotización queda intacta; solo cambia su status.
     */
    public function convertToOrder(Quote $quote, array $data): Order
    {
        return DB::transaction(function () use ($quote, $data) {
            $quote = Quote::lockForUpdate()->findOrFail($quote->id);

            if (!$quote->isConvertible()) {
                throw new \DomainException('Esta cotización no puede convertirse en orden (estado actual: ' . $quote->status . ').');
            }

            $quote->load(['quotePackages', 'quotePhotoTypes', 'services']);

            $orderData = array_merge($data, [
                'customer_id' => $quote->customer_id,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $quote->notes,
            ]);

            $order = $this->orders->createOrder($orderData, $quote);

            $quote->update(['status' => 'converted']);

            return $order->fresh(array_merge(OrderService::RELATIONS, ['quote:id,status']));
        });
    }

    public function updateStatus(Quote $quote, string $status): Quote
    {
        if ($quote->status === 'converted') {
            throw new \DomainException('No se puede cambiar el estado de una cotización ya convertida.');
        }

        $quote->update(['status' => $status]);

        return $quote;
    }

    protected function resolveCustomer(array $data): Customer
    {
        if (!empty($data['customer_id'])) {
            return Customer::findOrFail($data['customer_id']);
        }

        if (!empty($data['new_customer'])) {
            $rawName = trim($data['new_customer']['name']);
            $normalizedName = mb_strtolower(preg_replace('/\s+/', ' ', $rawName));

            $customer = Customer::whereRaw('LOWER(name) = ?', [$normalizedName])
                ->lockForUpdate()
                ->first();

            if (!$customer) {
                $customer = Customer::create([
                    'name' => $rawName,
                    'phone' => $data['new_customer']['phone'] ?? null,
                    'email' => $data['new_customer']['email'] ?? null,
                ]);
            }

            return $customer;
        }

        throw new \DomainException('Debe seleccionar un cliente existente o crear uno nuevo.');
    }

    protected function ensureHasContent(array $photoTypes, array $packageIds): void
    {
        if (empty($photoTypes) && empty($packageIds)) {
            throw new \DomainException('Agrega al menos un tipo de fotografía o un paquete escolar.');
        }
    }

    public function patchQuote(Quote $quote, array $data): Quote
    {
        if ($quote->status === 'converted') {
            throw new \DomainException('No se puede editar una cotización ya convertida en orden.');
        }

        $quote->update([
            'valid_until' => array_key_exists('valid_until', $data) ? $data['valid_until'] : $quote->valid_until,
            'notes' => array_key_exists('notes', $data) ? $data['notes'] : $quote->notes,
        ]);

        return $quote->fresh(self::RELATIONS);
    }

    /**
     * Líneas con `id` conservan su precio; sin `id` se tratan como nuevas (precio vigente).
     * Paquetes ya presentes conservan su snapshot. Si no viene additional_services, se conservan.
     */
    public function updateConcepts(Quote $quote, array $data): Quote
    {
        $this->ensureHasContent($data['photo_types'] ?? [], $data['package_ids'] ?? []);

        return DB::transaction(function () use ($quote, $data) {
            $quote = Quote::lockForUpdate()->findOrFail($quote->id);

            if ($quote->status === 'converted') {
                throw new \DomainException('No se puede editar una cotización ya convertida en orden.');
            }

            $this->content->syncFor($quote, $data);
            $quote->update(['total' => $this->content->recalculate($quote)]);

            return $quote->fresh(self::RELATIONS);
        });
    }

    public function updateCustomer(Quote $quote, array $data): Quote
    {
        if (!empty($data['customer_id'])) {
            $customerId = $data['customer_id'];
        } elseif (!empty($data['new_customer'])) {
            $customer = Customer::create([
                'name'  => $data['new_customer']['name'],
                'phone' => $data['new_customer']['phone'] ?? null,
                'email' => $data['new_customer']['email'] ?? null,
            ]);
            $customerId = $customer->id;
        } else {
            throw new \DomainException('Debes proporcionar un cliente existente o uno nuevo.');
        }

        $quote->customer_id = $customerId;
        $quote->save();

        return $quote->fresh(array_merge(self::RELATIONS, ['order:id,quote_id,status,total,balance_due']));
    }
}