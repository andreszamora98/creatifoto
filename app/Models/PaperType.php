<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaperType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'extra_price',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
    ];

    /**
     * Catálogo: paper_type_id es restrictOnDelete en order_photo_type,
     * protege que no se borre un papel que ya está en uso.
     */
    public function orderPhotoTypes(): HasMany
    {
        return $this->hasMany(OrderPhotoType::class);
    }
}