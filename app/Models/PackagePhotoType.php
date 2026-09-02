<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Plantilla del paquete: cuántas fotos de cada tipo incluye.
 * Único por (package_id, photo_type_id) para evitar duplicados lógicos.
 */
class PackagePhotoType extends Pivot
{
    public $incrementing = true;

    protected $fillable = [
        'package_id',
        'photo_type_id',
        'quantity',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function photoType(): BelongsTo
    {
        return $this->belongsTo(PhotoType::class);
    }
}