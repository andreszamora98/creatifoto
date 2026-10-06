<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'total',
        'notes',
        'valid_until',
        'status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
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

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'quote_packages')
            ->withPivot(['package_name_snapshot', 'price_snapshot'])
            ->withTimestamps();
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function isConvertible(): bool
    {
        return in_array($this->status, ['pending', 'accepted']);
    }

    public function additionalServices(): MorphToMany
    {
        return $this->morphToMany(AdditionalService::class, 'serviceable')
            ->withPivot(['price', 'quantity', 'notes'])
            ->withTimestamps()
            ->using(Serviceable::class);
    }
}