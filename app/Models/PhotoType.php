<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PhotoType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'base_price',
        'base_quantity',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    /**
     * Catálogo: photo_type_id es restrictOnDelete en package_photo_type,
     * así que no se puede borrar un tipo de foto mientras algún
     * paquete lo siga usando.
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_photo_type')
            ->withPivot('quantity')
            ->withTimestamps()
            ->using(PackagePhotoType::class);
    }

    /**
     * Líneas de "paquete personalizado" capturadas en órdenes.
     * photo_type_id también es restrictOnDelete aquí.
     */
    public function orderPhotoTypes(): HasMany
    {
        return $this->hasMany(OrderPhotoType::class);
    }

    protected static function booted()
    {
        static::creating(function ($photoType) {
            $photoType->slug = Str::slug($photoType->name);
        });
    }
}