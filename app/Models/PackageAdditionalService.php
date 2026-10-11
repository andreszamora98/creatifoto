<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PackageAdditionalService extends Pivot
{
    public $incrementing = true;

    protected $fillable = [
        'package_id',
        'additional_service_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class)->withTrashed();
    }

    public function additionalService(): BelongsTo
    {
        return $this->belongsTo(AdditionalService::class)->withTrashed();
    }
}