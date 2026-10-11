<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PackagePhotoType extends Pivot
{
    public $incrementing = true;

    protected $fillable = [
        'package_id',
        'photo_type_id',
        'quantity',
        'format',
        'paper_type_id',
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

    public function photoType(): BelongsTo
    {
        return $this->belongsTo(PhotoType::class)->withTrashed();
    }

    public function paperType(): BelongsTo
    {
        return $this->belongsTo(PaperType::class)->withTrashed();
    }
}