<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'payment_method',
        'type',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Historial de abonos/pagos de una orden. order_id es cascadeOnDelete.
     * 'type' distingue si el pago corresponde al anticipo ('advance')
     * o a la liquidación final ('settlement').
     *
     * orders.balance_due se mantiene sincronizado automáticamente por
     * PaymentObserver cada vez que se crea/edita/elimina un Payment,
     * sumando SUM(payments.amount) y restándolo de orders.total.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}