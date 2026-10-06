<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Serviceable extends MorphPivot
{
    protected $table = 'serviceables';

    public $incrementing = true;

    protected $fillable = [
        'additional_service_id',
        'serviceable_id',
        'serviceable_type',
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
        return $this->belongsTo(AdditionalService::class);
    }

    public function serviceable(): MorphTo
    {
        return $this->morphTo();
    }
}