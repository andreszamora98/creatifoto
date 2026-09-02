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
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Historial de abonos. order_id es cascadeOnDelete.
     * El saldo (debe) NUNCA se guarda como columna: se calcula como
     * orders.total - SUM(payments.amount) para que nunca se
     * desincronice (ver Order::balance()).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}