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
}