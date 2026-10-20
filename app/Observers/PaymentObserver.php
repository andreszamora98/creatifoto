<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $this->recalculate($payment);
    }

    public function updated(Payment $payment): void
    {
        $this->recalculate($payment);
    }

    public function deleted(Payment $payment): void
    {
        $this->recalculate($payment);
    }

    private function recalculate(Payment $payment): void
    {
        $orderId = $payment->order_id;

        if (!$orderId) {
            return;
        }

        DB::transaction(function () use ($orderId) {
            $order = Order::lockForUpdate()->find($orderId);

            if (!$order) {
                return;
            }

            $totalPaid = (float) $order->payments()->sum('amount');
            $netTotal = (float) $order->total - (float) $order->discount_amount;

            $order->balance_due = round($netTotal - $totalPaid, 2);
            $order->save();
        });
    }
}