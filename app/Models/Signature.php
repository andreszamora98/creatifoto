<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Signature extends Model
{
    use HasFactory;

    protected $fillable = [
        'signature_image_path',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    /**
     * En teoría es 1:1 con Order (una firma pertenece a una sola orden),
     * pero mientras no exista un ->unique() sobre orders.signature_id,
     * nada impide que dos órdenes compartan la misma firma. Se modela
     * como hasOne asumiendo que esa unicidad se agregará/valida aparte.
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}