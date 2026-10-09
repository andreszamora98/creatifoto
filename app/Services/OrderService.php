<?php

namespace App\Services;

use App\Models\Customer;
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
    ];

    public function __construct(protected ContentSnapshotService $content)
    {
    }

    /**
     * Crea una orden. Si viene $fromQuote, el contenido se copia tal cual de la cotización
     * (sin consultar el catálogo); si no, se arma desde el payload con precios vigentes.
     */
    public function createOrder(array $data, ?Quote $fromQuote = null): Order
    {
        return DB::transaction(function () use ($data, $fromQuote) {
            $customer = $this->resolveCustomer($data);

            $order = Order::create([
                'customer_id' => $customer->id,
                'quote_id' => $fromQuote?->id,
                'total' => 0,
                'initial_total' => 0,
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

            $total = $this->content->recalculate($order);
            $totalPayments = (float) collect($data['payments'])->sum('amount');

            if (round($totalPayments, 2) > round($total, 2)) {
                throw new \DomainException(
                    'La suma de los pagos no puede exceder el total de la orden ($' . number_format($total, 2) . ').'
                );
            }

            $order->forceFill([
                'total' => $total,
                'initial_total' => $total,
                'balance_due' => $total,
            ])->save();

            $accumulated = 0;
            foreach ($data['payments'] as $i => $paymentData) {
                $accumulated += $paymentData['amount'];
                $isSettlement = round($accumulated, 2) >= round($total, 2);

                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $paymentData['amount'],
                    'payment_method' => $paymentData['payment_method'] ?? null,
                    'type' => $isSettlement ? 'settlement' : 'advance',
                    'paid_at' => now(),
                    'notes' => 'Pago ' . ($i + 1) . ($isSettlement ? ' (liquidación)' : ' (abono)'),
                ]);
            }

            return $order->refresh()->load(self::RELATIONS);
        });
    }

    /**
     * Edita el contenido de una orden. Las líneas existentes (con id) conservan su precio;
     * las nuevas toman el catálogo vigente. El total se recalcula desde las filas de la orden.
     */
    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $order = Order::lockForUpdate()->findOrFail($order->id);

            if ($order->status === 'delivered') {
                throw new \DomainException('Una orden entregada no admite modificaciones.');
            }

            $this->content->syncFor($order, $data);

            $total = $this->content->recalculate($order);
            $paid = round((float) $order->payments()->sum('amount'), 2);

            if ($total < $paid) {
                throw new \DomainException(
                    'El nuevo total ($' . number_format($total, 2) . ') es menor a lo ya pagado ($'
                    . number_format($paid, 2) . ').'
                );
            }

            $order->fill([
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $order->notes,
                'estimated_delivery_date' => $data['estimated_delivery_date'] ?? $order->estimated_delivery_date,
                'estimated_delivery_time' => $data['estimated_delivery_time'] ?? $order->estimated_delivery_time,
            ]);
            $order->forceFill([
                'total' => $total,
                'balance_due' => round($total - $paid, 2),
            ])->save();

            return $order->fresh(self::RELATIONS);
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

        throw new \DomainException('Debe seleccionar un cliente existente o crear uno nuevo.');
    }

    public function addPayment(Order $order, array $data): array
    {
        if (round((float) $data['amount'], 2) > round((float) $order->balance_due, 2)) {
            throw new \DomainException(
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
            throw new \DomainException(
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