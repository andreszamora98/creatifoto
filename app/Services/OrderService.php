<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public const RELATIONS = [
        'customer:id,name,phone,email',
        'orderPackages',
        'orderPhotoTypes',
        'services',
        'payments',
        'pickups.customer:id,name,phone,email',
    ];

    public function __construct(
        protected ContentSnapshotService $content,
        protected CustomerResolver $customerResolver,
        protected AutomaticDiscountService $autoDiscount
    ) {
    }

    public function createOrder(array $data, ?Quote $fromQuote = null): Order
    {
        return DB::transaction(function () use ($data, $fromQuote) {
            $customer = $this->customerResolver->resolve($data);

            $source = array_key_exists('discount_source', $data) && $data['discount_source'] !== null
                ? $data['discount_source']
                : ($fromQuote ? $fromQuote->discount_source : 'auto');

            $appliesToPackages = array_key_exists('discount_applies_to_packages', $data)
                ? (bool) $data['discount_applies_to_packages']
                : (bool) ($fromQuote->discount_applies_to_packages ?? false);

            $order = Order::create([
                'customer_id' => $customer->id,
                'quote_id' => $fromQuote?->id,
                'total' => 0,
                'initial_total' => 0,
                'discount_type' => null,
                'discount_value' => null,
                'discount_amount' => 0,
                'discount_reason' => null,
                'discount_source' => $source,
                'discount_applies_to_packages' => $appliesToPackages,
                'auto_discount_reason' => null,
                'pose_number' => $data['pose_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'estimated_delivery_date' => $data['estimated_delivery_date'],
                'estimated_delivery_time' => $data['estimated_delivery_time'],
                'actual_delivery_date' => null,
                'status' => 'pending',
                'signature_id' => null,
            ]);

            if ($fromQuote) {
                $this->content->copyQuoteToOrder($fromQuote, $order);
            } else {
                $this->content->createFor($order, $data);
            }

            $discountType = null;
            $discountValue = null;
            $discountReason = null;
            $autoReason = null;

            if ($source === 'auto') {
                $eval = $this->autoDiscount->evaluate($order);
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

            $calc = $this->content->recalculateWithDiscount($order, $discountType, $discountValue, $appliesToPackages);
            $netTotal = $calc['net_total'];

            $totalPayments = (float) collect($data['payments'] ?? [])->sum('amount');

            if (round($totalPayments, 2) > round($netTotal, 2)) {
                throw new \DomainException(
                    'La suma de los pagos no puede exceder el total de la orden ($' . number_format($netTotal, 2) . ').'
                );
            }

            $order->forceFill([
                'total' => $calc['total'],
                'initial_total' => $calc['total'],
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $calc['discount_amount'],
                'discount_reason' => $discountReason,
                'auto_discount_reason' => $autoReason,
                'balance_due' => $netTotal,
            ])->save();

            $accumulated = 0;
            foreach ($data['payments'] ?? [] as $i => $paymentData) {
                $accumulated += $paymentData['amount'];
                $isSettlement = round($accumulated, 2) >= round($netTotal, 2);

                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $paymentData['amount'],
                    'payment_method' => $paymentData['payment_method'] ?? null,
                    'type' => $isSettlement ? 'settlement' : 'advance',
                    'paid_at' => now(),
                    'notes' => 'Pago ' . ($i + 1) . ($isSettlement ? ' (liquidación)' : ' (abono)'),
                ]);
            }

            if (!empty($data['pickup_persons'])) {
                $this->persistPickups($order, $data['pickup_persons']);
            }

            return $order->refresh()->load(self::RELATIONS);
        });
    }

    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $order = Order::lockForUpdate()->findOrFail($order->id);

            if ($order->status === 'delivered') {
                throw new \DomainException('Una orden entregada no admite modificaciones.');
            }

            $this->content->syncFor($order, $data);

            $source = array_key_exists('discount_source', $data)
                ? $data['discount_source']
                : $order->discount_source;

            $appliesToPackages = array_key_exists('discount_applies_to_packages', $data)
                ? (bool) $data['discount_applies_to_packages']
                : (bool) $order->discount_applies_to_packages;

            $discountType = null;
            $discountValue = null;
            $discountReason = null;
            $autoReason = null;

            if ($source === 'auto') {
                $eval = $this->autoDiscount->evaluate($order);
                if ($eval['eligible']) {
                    $discountType = 'percent';
                    $discountValue = (float) config('creatifoto.auto_discount_percent', 10);
                    $discountReason = $eval['reason'];
                    $autoReason = $eval['reason'];
                }
            } elseif ($source === 'manual') {
                $discountType = array_key_exists('discount_type', $data)
                    ? $data['discount_type']
                    : $order->discount_type;
                $discountValue = array_key_exists('discount_value', $data)
                    ? ($data['discount_value'] !== null ? (float) $data['discount_value'] : null)
                    : ($order->discount_value !== null ? (float) $order->discount_value : null);
                $discountReason = array_key_exists('discount_reason', $data)
                    ? $data['discount_reason']
                    : $order->discount_reason;
            }

            $calc = $this->content->recalculateWithDiscount($order, $discountType, $discountValue, $appliesToPackages);
            $netTotal = $calc['net_total'];
            $paid = round((float) $order->payments()->sum('amount'), 2);

            if ($netTotal < $paid) {
                throw new \DomainException(
                    'El nuevo total ($' . number_format($netTotal, 2) . ') es menor a lo ya pagado ($'
                    . number_format($paid, 2) . ').'
                );
            }

            $order->fill([
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $order->notes,
                'estimated_delivery_date' => array_key_exists('estimated_delivery_date', $data)
                    ? $data['estimated_delivery_date'] : $order->estimated_delivery_date,
                'estimated_delivery_time' => array_key_exists('estimated_delivery_time', $data)
                    ? $data['estimated_delivery_time'] : $order->estimated_delivery_time,
            ]);

            $order->forceFill([
                'total' => $calc['total'],
                'discount_source' => $source,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $calc['discount_amount'],
                'discount_reason' => $discountReason,
                'discount_applies_to_packages' => $appliesToPackages,
                'auto_discount_reason' => $autoReason,
                'balance_due' => round($netTotal - $paid, 2),
            ])->save();

            return $order->fresh(self::RELATIONS);
        });
    }

    public function updateMetadata(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $order = Order::lockForUpdate()->findOrFail($order->id);

            if ($order->status === 'delivered') {
                throw new \DomainException('Una orden entregada no admite modificaciones.');
            }

            $order->fill([
                'pose_number' => array_key_exists('pose_number', $data) ? $data['pose_number'] : $order->pose_number,
                'archive_name' => array_key_exists('archive_name', $data) ? $data['archive_name'] : $order->archive_name,
                'pose_range' => array_key_exists('pose_range', $data) ? $data['pose_range'] : $order->pose_range,
            ])->save();

            return $order->fresh(self::RELATIONS);
        });
    }

    public function addPayment(Order $order, array $data): array
    {
        return DB::transaction(function () use ($order, $data) {
            $order = Order::lockForUpdate()->findOrFail($order->id);

            $amount = round((float) $data['amount'], 2);
            $netTotal = round((float) $order->total - (float) $order->discount_amount, 2);
            $paid = round((float) $order->payments()->sum('amount'), 2);
            $newPaid = round($paid + $amount, 2);

            if ($newPaid > $netTotal) {
                $available = round($netTotal - $paid, 2);

                throw new \DomainException(
                    'El monto excede el saldo pendiente de la orden ($' . number_format($available, 2) . ').'
                );
            }

            $isSettlement = $newPaid >= $netTotal;

            Payment::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? null,
                'type' => $isSettlement ? 'settlement' : 'advance',
                'notes' => $data['notes'] ?? null,
                'paid_at' => now(),
            ]);

            $order->refresh();

            return [
                'message' => 'Pago registrado correctamente',
                'payment' => $order->payments()->latest('id')->first(),
                'order_balance_due' => $order->balance_due,
            ];
        });
    }

    public function markAsDelivered(Order $order): array
    {
        return DB::transaction(function () use ($order) {
            $order = Order::lockForUpdate()->findOrFail($order->id);

            if ($order->status === 'delivered') {
                throw new \DomainException('Esta orden ya fue entregada.');
            }

            if ((float) $order->balance_due > 0) {
                throw new \DomainException(
                    'No se puede entregar la orden: tiene un saldo pendiente de $' . number_format($order->balance_due, 2) . '.'
                );
            }

            $order->update([
                'status' => 'delivered',
                'actual_delivery_date' => now(),
            ]);

            return [
                'message' => 'Orden entregada correctamente',
                'order' => $order->fresh(self::RELATIONS),
            ];
        });
    }

    public function syncOrderPickups(Order $order, array $items): Order
    {
        return DB::transaction(function () use ($order, $items) {
            $order = Order::lockForUpdate()->findOrFail($order->id);

            if ($order->status === 'delivered') {
                throw new \DomainException('Una orden entregada no admite modificaciones.');
            }

            $this->persistPickups($order, $items);

            return $order->fresh(self::RELATIONS);
        });
    }

    protected function persistPickups(Order $order, array $items): void
    {
        $kept = [];

        foreach ($items as $item) {
            $customer = $this->customerResolver->resolve([
                'customer_id' => $item['customer_id'] ?? null,
                'new_customer' => $item['new_customer'] ?? null,
            ]);

            if ((int) $customer->id === (int) $order->customer_id) {
                continue;
            }

            $order->pickups()->updateOrCreate(
                ['customer_id' => $customer->id],
                [
                    'name_snapshot' => $customer->name,
                    'relationship' => $item['relationship'] ?? null,
                ]
            );

            $kept[] = $customer->id;
        }

        $order->pickups()->whereNotIn('customer_id', $kept)->delete();
    }
}