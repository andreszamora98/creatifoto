<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Paquete escolar vendido dentro de una orden.
 * package_name_snapshot / price_snapshot se congelan al vender:
 * si el paquete cambia de precio o nombre después, esta línea no
 * cambia retroactivamente.
 */
class OrderPackage extends Pivot
{
    public $incrementing = true;

    protected $fillable = [
        'order_id',
        'package_id',
        'package_name_snapshot',
        'price_snapshot',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}