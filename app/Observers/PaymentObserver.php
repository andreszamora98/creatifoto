<?php

namespace App\Observers;

use App\Models\Payment;

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
        $order = $payment->order;
        $totalPaid = $order->payments()->sum('amount');

        $order->balance_due = round($order->total - $totalPaid, 2);
        $order->save();
    }
}