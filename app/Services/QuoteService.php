<?php

namespace App\Services;

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
        protected OrderService $orders,
        protected CustomerResolver $customerResolver,
        protected AutomaticDiscountService $autoDiscount
    ) {
    }

    public function createQuote(array $data): Quote
    {
        return DB::transaction(function () use ($data) {
            $this->ensureHasContent($data['photo_types'] ?? [], $data['package_ids'] ?? []);

            $customer = $this->customerResolver->resolve($data);

            $source = array_key_exists('discount_source', $data) && $data['discount_source'] !== null
                ? $data['discount_source']
                : 'auto';
            $appliesToPackages = (bool) ($data['discount_applies_to_packages'] ?? false);

            $quote = Quote::create([
                'customer_id' => $customer->id,
                'total' => 0,
                'discount_type' => null,
                'discount_value' => null,
                'discount_amount' => 0,
                'discount_reason' => null,
                'discount_source' => $source,
                'discount_applies_to_packages' => $appliesToPackages,
                'auto_discount_reason' => null,
                'notes' => $data['notes'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'status' => 'pending',
            ]);

            $this->content->createFor($quote, $data);

            $discountType = null;
            $discountValue = null;
            $discountReason = null;
            $autoReason = null;

            if ($source === 'auto') {
                $eval = $this->autoDiscount->evaluate($quote);
                if ($eval['eligible']) {
                    $discountType = 'percent';
                    $discountValue = (float) config('creatifoto.auto_discount_percent', 10);
                    $discountReason = $eval['reason'];
                    $autoReason = $eval['reason'];
                }
            } elseif ($source === 'manual') {
                $discountType = $data['discount_type'] ?? null;
                $discountValue = isset($data['discount_value']) && $data['discount_value'] !== null
                    ? (float) $data['discount_value']
                    : null;
                $discountReason = $data['discount_reason'] ?? null;
            }

            $calc = $this->content->recalculateWithDiscount($quote, $discountType, $discountValue, $appliesToPackages);

            $quote->update([
                'total' => $calc['total'],
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $calc['discount_amount'],
                'discount_reason' => $discountReason,
                'auto_discount_reason' => $autoReason,
            ]);

            return $quote->fresh(self::RELATIONS);
        });
    }

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
        return DB::transaction(function () use ($quote, $status) {
            $quote = Quote::lockForUpdate()->findOrFail($quote->id);

            if ($quote->status === 'converted') {
                throw new \DomainException('No se puede cambiar el estado de una cotización ya convertida.');
            }

            $quote->update(['status' => $status]);

            return $quote->fresh(self::RELATIONS);
        });
    }

    public function patchQuote(Quote $quote, array $data): Quote
    {
        return DB::transaction(function () use ($quote, $data) {
            $quote = Quote::lockForUpdate()->findOrFail($quote->id);

            if ($quote->status === 'converted') {
                throw new \DomainException('No se puede editar una cotización ya convertida en orden.');
            }

            $quote->fill([
                'valid_until' => array_key_exists('valid_until', $data) ? $data['valid_until'] : $quote->valid_until,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $quote->notes,
            ])->save();

            return $quote->fresh(self::RELATIONS);
        });
    }

    public function updateConcepts(Quote $quote, array $data): Quote
    {
        $this->ensureHasContent($data['photo_types'] ?? [], $data['package_ids'] ?? []);

        return DB::transaction(function () use ($quote, $data) {
            $quote = Quote::lockForUpdate()->findOrFail($quote->id);

            if ($quote->status === 'converted') {
                throw new \DomainException('No se puede editar una cotización ya convertida.');
            }

            $this->content->syncFor($quote, $data);

            $source = array_key_exists('discount_source', $data)
                ? $data['discount_source']
                : $quote->discount_source;

            $appliesToPackages = array_key_exists('discount_applies_to_packages', $data)
                ? (bool) $data['discount_applies_to_packages']
                : (bool) $quote->discount_applies_to_packages;

            $discountType = null;
            $discountValue = null;
            $discountReason = null;
            $autoReason = null;

            if ($source === 'auto') {
                $eval = $this->autoDiscount->evaluate($quote);
                if ($eval['eligible']) {
                    $discountType = 'percent';
                    $discountValue = (float) config('creatifoto.auto_discount_percent', 10);
                    $discountReason = $eval['reason'];
                    $autoReason = $eval['reason'];
                }
            } elseif ($source === 'manual') {
                $discountType = array_key_exists('discount_type', $data)
                    ? $data['discount_type']
                    : $quote->discount_type;
                $discountValue = array_key_exists('discount_value', $data)
                    ? ($data['discount_value'] !== null ? (float) $data['discount_value'] : null)
                    : ($quote->discount_value !== null ? (float) $quote->discount_value : null);
                $discountReason = array_key_exists('discount_reason', $data)
                    ? $data['discount_reason']
                    : $quote->discount_reason;
            }

            $calc = $this->content->recalculateWithDiscount($quote, $discountType, $discountValue, $appliesToPackages);

            $quote->update([
                'total' => $calc['total'],
                'discount_source' => $source,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $calc['discount_amount'],
                'discount_reason' => $discountReason,
                'discount_applies_to_packages' => $appliesToPackages,
                'auto_discount_reason' => $autoReason,
            ]);

            return $quote->fresh(self::RELATIONS);
        });
    }

    public function updateCustomer(Quote $quote, array $data): Quote
    {
        return DB::transaction(function () use ($quote, $data) {
            $quote = Quote::lockForUpdate()->findOrFail($quote->id);

            if ($quote->status === 'converted') {
                throw new \DomainException('No se puede editar una cotización ya convertida en orden.');
            }

            $customer = $this->customerResolver->resolve($data);

            $quote->update(['customer_id' => $customer->id]);

            return $quote->fresh(array_merge(self::RELATIONS, ['order:id,quote_id,status,total,balance_due']));
        });
    }

    protected function ensureHasContent(array $photoTypes, array $packageIds): void
    {
        if (empty($photoTypes) && empty($packageIds)) {
            throw new \DomainException('Agrega al menos un tipo de fotografía o un paquete escolar.');
        }
    }
}