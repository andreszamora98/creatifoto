<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use App\Models\PhotoType;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $customer = $this->resolveCustomer($data);
            $total = $this->calculateTotal($data);
            $totalPayments = collect($data['payments'])->sum('amount');
            if ($totalPayments > $total) {
                throw new \Exception(
                    'La suma de los pagos no puede exceder el total de la orden ($' . number_format($total, 2) . ').'
                );
            }
            $order = Order::create([
                'customer_id' => $customer->id,
                'total' => $total,
                'pose_number' => $data['pose_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'estimated_delivery_date' => $data['estimated_delivery_date'],
                'estimated_delivery_time' => $data['estimated_delivery_time'],
                'actual_delivery_date' => null,
                'status' => 'pending',
                'signature_id' => null,
            ]);

            $order->balance_due = $total;
            $order->save();

            foreach ($data['photo_types'] as $line) {
                $photoType = PhotoType::find($line['photo_type_id']);
                $order->orderPhotoTypes()->create([
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
                    $order->packages()->attach($pkg->id, [
                        'package_name_snapshot' => $pkg->name ?: ('Paquete ' . $pkg->package_number),
                        'price_snapshot' => $pkg->total_price,
                    ]);
                }
            }

            $accumulated = 0;
            foreach ($data['payments'] as $i => $paymentData) {
                $accumulated += $paymentData['amount'];
                $isSettlement = $accumulated >= $total;

                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $paymentData['amount'],
                    'payment_method' => $paymentData['payment_method'] ?? null,
                    'type' => $isSettlement ? 'settlement' : 'advance',
                    'paid_at' => now(),
                    'notes' => 'Pago ' . ($i + 1) . ($isSettlement ? ' (liquidación)' : ' (abono)'),
                ]);
            }

            $order->load([
                'customer:id,name,phone',
                'packages:id,package_number,name,total_price',
                'orderPhotoTypes.photoType:id,name,base_price',
                'orderPhotoTypes.paperType:id,name,extra_price',
                'payments',
            ]);
            $order->refresh();

            return $order;
        });
    }

    protected function resolveCustomer(array $data): Customer
    {
        if (!empty($data['customer_id'])) {
            $customer = Customer::findOrFail($data['customer_id']);
            if (!empty($data['contact_update'])) {
                $customer->update(array_filter([
                    'phone' => $data['contact_update']['phone'] ?? null,
                    'email' => $data['contact_update']['email'] ?? null,
                ], fn($value) => $value !== null));
            }
            return $customer;
        }

        if (!empty($data['new_customer'])) {
            $rawName = trim($data['new_customer']['name']);
            $normalizedName = mb_strtolower(preg_replace('/\s+/', ' ', $rawName));

            $customer = Customer::whereRaw('LOWER(name) = ?', [$normalizedName])
                ->lockForUpdate()
                ->first();

            if (!$customer) {
                try {
                    $customer = Customer::create([
                        'name' => $rawName,
                        'phone' => $data['new_customer']['phone'] ?? null,
                        'email' => $data['new_customer']['email'] ?? null,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                        $customer = Customer::whereRaw('LOWER(name) = ?', [$normalizedName])->first();
                        if (!$customer) {
                            throw $e;
                        }
                    } else {
                        throw $e;
                    }
                }
            }
            return $customer;
        }

        throw new \Exception('Debe seleccionar un cliente existente o crear uno nuevo.');
    }

    protected function calculateTotal(array $data): float
    {
        $total = 0;

        foreach ($data['photo_types'] as $line) {
            $photoType = PhotoType::find($line['photo_type_id']);
            $total += ($photoType->base_price ?? 0) * $line['quantity'];
        }

        if (!empty($data['package_ids'])) {
            $packages = Package::whereIn('id', $data['package_ids'])->get();
            $total += $packages->sum('total_price');
        }

        return $total;
    }

    public function addPayment(Order $order, array $data): array
    {
        if ($data['amount'] > $order->balance_due) {
            throw new \Exception(
                'El monto excede el saldo pendiente de la orden ($' . number_format($order->balance_due, 2) . ').'
            );
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? null,
            'type' => $data['type'],
            'notes' => $data['notes'] ?? null,
            'paid_at' => now(),
        ]);

        $order->refresh();

        return [
            'message' => 'Pago registrado correctamente',
            'payment' => $payment,
            'order_balance_due' => $order->balance_due,
        ];
    }

    public function markAsDelivered(Order $order): array
    {
        if ($order->balance_due > 0) {
            throw new \Exception(
                'No se puede entregar la orden: tiene un saldo pendiente de $' . number_format($order->balance_due, 2) . '.'
            );
        }

        $order->update([
            'status' => 'delivered',
            'actual_delivery_date' => now(),
        ]);

        return ['message' => 'Orden entregada correctamente', 'order' => $order];
    }
}