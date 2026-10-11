<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Serviceable extends Model
{
    protected $table = 'serviceables';

    protected $fillable = [
        'additional_service_id',
        'serviceable_id',
        'serviceable_type',
        'order_package_id',
        'quote_package_id',
        'additional_service_name_snapshot',
        'price',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function additionalService(): BelongsTo
    {
        return $this->belongsTo(AdditionalService::class)->withTrashed();
    }

    public function serviceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function orderPackage(): BelongsTo
    {
        return $this->belongsTo(OrderPackage::class);
    }

    public function quotePackage(): BelongsTo
    {
        return $this->belongsTo(QuotePackage::class);
    }
}