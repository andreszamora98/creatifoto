<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'total',
        'discount_type',
        'discount_value',
        'discount_amount',
        'discount_reason',
        'discount_source',
        'discount_applies_to_packages',
        'auto_discount_reason',
        'notes',
        'valid_until',
        'status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_applies_to_packages' => 'boolean',
        'valid_until' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotePhotoTypes(): HasMany
    {
        return $this->hasMany(QuotePhotoType::class);
    }

    public function quotePackages(): HasMany
    {
        return $this->hasMany(QuotePackage::class);
    }

    public function services(): MorphMany
    {
        return $this->morphMany(Serviceable::class, 'serviceable');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function isConvertible(): bool
    {
        return in_array($this->status, ['pending', 'accepted']);
    }
}