<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class OrderPhotoType extends Model
{
    use HasFactory;
    
    protected $table = 'order_photo_type';  

    protected $fillable = [
        'order_id',
        'photo_type_id',
        'quantity',
        'unit_price',
        'format',
        'paper_type_id',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    /**
     * "Paquete personalizado": línea capturada libremente por el
     * recepcionista. order_id es cascadeOnDelete.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * photo_type_id es restrictOnDelete (protege el catálogo).
     */
    public function photoType(): BelongsTo
    {
        return $this->belongsTo(PhotoType::class);
    }

    /**
     * paper_type_id es nullable y restrictOnDelete.
     */
    public function paperType(): BelongsTo
    {
        return $this->belongsTo(PaperType::class);
    }

    /**
     * Indicaciones adicionales aplicadas a esta línea específica
     * (en vez de a la orden completa).
     */
    public function additionalServices(): MorphToMany
    {
        return $this->morphToMany(AdditionalService::class, 'serviceable')
            ->withPivot(['price', 'notes'])
            ->withTimestamps()
            ->using(Serviceable::class);
    }
}