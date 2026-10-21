<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderPackage extends Model
{
    protected $fillable = [
        'order_id',
        'package_id',
        'school_id',
        'package_name_snapshot',
        'school_name_snapshot',
        'price_snapshot',
        'list_price_snapshot',
        'discount_amount_snapshot',
        'package_notes_snapshot',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'list_price_snapshot' => 'decimal:2',
        'discount_amount_snapshot' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class)->withTrashed();
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class)->withTrashed();
    }

    public function photoTypes(): HasMany
    {
        return $this->hasMany(OrderPhotoType::class, 'order_package_id');
    }
}