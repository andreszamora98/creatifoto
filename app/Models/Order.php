<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'quote_id',
        'total',
        'initial_total',
        'pose_number',
        'notes',
        'estimated_delivery_date',
        'estimated_delivery_time',
        'actual_delivery_date',
        'status',
        'signature_id',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'initial_total' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'estimated_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'estimated_delivery_time' => 'datetime:H:i',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function signature(): BelongsTo
    {
        return $this->belongsTo(Signature::class);
    }

    public function orderPhotoTypes(): HasMany
    {
        return $this->hasMany(OrderPhotoType::class);
    }

    public function orderPackages(): HasMany
    {
        return $this->hasMany(OrderPackage::class);
    }

    public function services(): MorphMany
    {
        return $this->morphMany(Serviceable::class, 'serviceable');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function balance(): float
    {
        return (float) $this->total - (float) $this->payments()->sum('amount');
    }
}