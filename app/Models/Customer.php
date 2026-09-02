<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    /**
     * Un cliente puede tener muchas órdenes a lo largo del tiempo.
     * Nota: orders.customer_id es cascadeOnDelete, pero como Customer
     * usa SoftDeletes, en la práctica nunca se hace un delete físico
     * (solo soft-delete), por lo que las órdenes quedan intactas.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}