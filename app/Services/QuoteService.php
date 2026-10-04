<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Package;
use App\Models\PhotoType;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function createQuote(array $data)
    {
        return DB::transaction(function () use ($data) {
            $this->ensureHasContent($data['photo_types'] ?? [], $data['package_ids'] ?? []);

            $customer = $this->resolveCustomer($data);
            $total = $this->calculateTotal($data);

            $quote = Quote::create([
                'customer_id' => $customer->id,
                'total' => $total,
                'notes' => $data['notes'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($data['photo_types'] ?? [] as $line) {
                $photoType = PhotoType::find($line['photo_type_id']);
                $quote->quotePhotoTypes()->create([
                    'photo_type_id' => $line['photo_type_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $photoType->base_price ?? 0,
                    'format' => $line['format'],
                    'paper_type_id' => $line['paper_type_id'] ?? null,
                ]);
            }

            if (!empty($data['package_ids'])) {
                $packages = Package::whereIn('id', $data['package_ids'])->get();
                foreach ($packages as $pkg) {
                    $quote->packages()->attach($pkg->id, [
                        'package_name_snapshot' => $pkg->name ?: ('Paquete ' . $pkg->package_number),
                        'price_snapshot' => $pkg->total_price,
                    ]);
                }
            }

            $quote->load([
                'customer:id,name,phone,email',
                'packages:id,package_number,name,total_price',
                'quotePhotoTypes.photoType:id,name,base_price',
                'quotePhotoTypes.paperType:id,name,extra_price',
            ]);

            return $quote;
        });
    }

    public function convertToOrder(Quote $quote, array $data): Order
    {
        if (!$quote->isConvertible()) {
            throw new \DomainException('Esta cotización no puede convertirse en orden (estado actual: ' . $quote->status . ').');
        }

        return DB::transaction(function () use ($quote, $data) {
            $photoTypes = $data['photo_types'] ?? $this->quotePhotoTypesAsInput($quote);
            $packageIds = $data['package_ids'] ?? $quote->packages->pluck('id')->all();
            $notes = array_key_exists('notes', $data) ? $data['notes'] : $quote->notes;

            // El contenido puede venir de la base de datos, no solo de la validación
            $this->ensureHasContent($photoTypes, $packageIds);

            $this->syncQuoteContent($quote, $photoTypes, $packageIds, $notes);

            $orderData = array_merge($data, [
                'customer_id' => $quote->customer_id,
                'photo_types' => $photoTypes,
                'package_ids' => $packageIds,
                'notes' => $notes,
            ]);

            $order = app(OrderService::class)->createOrder($orderData);
            $order->update(['quote_id' => $quote->id]);

            $quote->update(['status' => 'converted']);

            return $order->fresh([
                'customer:id,name,phone',
                'quote:id,status',
                'packages:id,package_number,name,total_price',
                'orderPhotoTypes.photoType:id,name,base_price',
                'orderPhotoTypes.paperType:id,name,extra_price',
                'payments',
            ]);
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

    protected function syncQuoteContent(Quote $quote, array $photoTypes, array $packageIds, ?string $notes): void
    {
        $quote->quotePhotoTypes()->delete();

        foreach ($photoTypes as $line) {
            $photoType = PhotoType::find($line['photo_type_id']);
            $quote->quotePhotoTypes()->create([
                'photo_type_id' => $line['photo_type_id'],
                'quantity' => $line['quantity'],
                'unit_price' => $photoType->base_price ?? 0,
                'format' => $line['format'],
                'paper_type_id' => $line['paper_type_id'] ?? null,
            ]);
        }

        $quote->packages()->detach();
        if (!empty($packageIds)) {
            $packages = Package::whereIn('id', $packageIds)->get();
            foreach ($packages as $pkg) {
                $quote->packages()->attach($pkg->id, [
                    'package_name_snapshot' => $pkg->name ?: ('Paquete ' . $pkg->package_number),
                    'price_snapshot' => $pkg->total_price,
                ]);
            }
        }

        $total = $this->calculateTotal([
            'photo_types' => $photoTypes,
            'package_ids' => $packageIds,
        ]);

        $quote->update([
            'notes' => $notes,
            'total' => $total,
        ]);
    }

    protected function quotePhotoTypesAsInput(Quote $quote): array
    {
        return $quote->quotePhotoTypes->map(fn ($line) => [
            'photo_type_id' => $line->photo_type_id,
            'quantity' => $line->quantity,
            'format' => $line->format,
            'paper_type_id' => $line->paper_type_id,
        ])->all();
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

    protected function calculateTotal(array $data): float
    {
        $total = 0;

        foreach ($data['photo_types'] ?? [] as $line) {
            $photoType = PhotoType::find($line['photo_type_id']);
            $total += ($photoType->base_price ?? 0) * $line['quantity'];
        }

        if (!empty($data['package_ids'])) {
            $packages = Package::whereIn('id', $data['package_ids'])->get();
            $total += $packages->sum('total_price');
        }

        return (float) $total;
    }

    /**
     * Regla de negocio: al menos un tipo de fotografía O un paquete escolar.
     */
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

        return $quote->fresh([
            'customer:id,name,phone,email',
            'packages:id,package_number,name,total_price',
            'quotePhotoTypes.photoType:id,name,base_price',
            'quotePhotoTypes.paperType:id,name,extra_price',
        ]);
    }

    public function updateConcepts(Quote $quote, array $data): Quote
    {
        if ($quote->status === 'converted') {
            throw new \DomainException('No se puede editar una cotización ya convertida en orden.');
        }

        $this->ensureHasContent($data['photo_types'] ?? [], $data['package_ids'] ?? []);

        return DB::transaction(function () use ($quote, $data) {
            $total = $this->calculateTotal($data);

            $quote->update(['total' => $total]);

            $quote->quotePhotoTypes()->delete();
            foreach ($data['photo_types'] ?? [] as $line) {
                $photoType = PhotoType::find($line['photo_type_id']);
                $quote->quotePhotoTypes()->create([
                    'photo_type_id' => $line['photo_type_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $photoType->base_price ?? 0,
                    'format' => $line['format'],
                    'paper_type_id' => $line['paper_type_id'] ?? null,
                ]);
            }

            $syncData = [];
            if (!empty($data['package_ids'])) {
                $packages = Package::whereIn('id', $data['package_ids'])->get();
                foreach ($packages as $pkg) {
                    $syncData[$pkg->id] = [
                        'package_name_snapshot' => $pkg->name ?: ('Paquete ' . $pkg->package_number),
                        'price_snapshot' => $pkg->total_price,
                    ];
                }
            }
            $quote->packages()->sync($syncData);

            return $quote->fresh([
                'customer:id,name,phone,email',
                'packages:id,package_number,name,total_price',
                'quotePhotoTypes.photoType:id,name,base_price',
                'quotePhotoTypes.paperType:id,name,extra_price',
            ]);
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

        return $quote->fresh()->load([
            'customer:id,name,phone,email',
            'packages:id,package_number,name,total_price',
            'quotePhotoTypes.photoType:id,name,base_price',
            'quotePhotoTypes.paperType:id,name,extra_price',
            'order:id,quote_id,status,total,balance_due',
        ]);
    }
}