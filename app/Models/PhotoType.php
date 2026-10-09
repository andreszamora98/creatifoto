<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PhotoType extends Model
{
    use HasFactory, SoftDeletes;

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
     * Catálogo con soft-delete. photo_type_id sigue siendo restrictOnDelete en las tablas
     * históricas, así que un hard-delete accidental queda bloqueado.
     * (Corregido: la tabla pivote se llama package_photo_types, en plural.)
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_photo_types')
            ->withPivot('quantity')
            ->withTimestamps()
            ->using(PackagePhotoType::class);
    }

    /** Líneas históricas de órdenes que usan este tipo de foto. */
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