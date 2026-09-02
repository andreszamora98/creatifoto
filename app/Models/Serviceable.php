<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

/**
 * Pivote polimórfico de las tablas serviceables.
 *
 * serviceable_id / serviceable_type apuntan al "dueño" de la
 * indicación adicional: una Order completa o una línea OrderPhotoType.
 * Se modela como su propia clase (en vez de dejar que morphToMany use
 * el pivot genérico) porque price y notes son datos de negocio reales,
 * no solo columnas de acompañamiento.
 *
 * La regla "si aplica a nivel orden se bloquean las individuales" NO
 * vive aquí ni en la base de datos: es validación de formulario/frontend.
 */
class Serviceable extends MorphPivot
{
    protected $table = 'serviceables';

    // Esta tabla sí tiene id propio (no es una pivot compuesta estándar).
    public $incrementing = true;

    protected $fillable = [
        'additional_service_id',
        'serviceable_id',
        'serviceable_type',
        'price',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function additionalService(): BelongsTo
    {
        return $this->belongsTo(AdditionalService::class);
    }

    /**
     * El modelo "dueño": Order u OrderPhotoType.
     */
    public function serviceable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}