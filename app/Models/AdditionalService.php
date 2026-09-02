<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class AdditionalService extends Model
{
    use HasFactory;

    // La tabla solo tiene id + timestamps por ahora; agrega aquí
    // los campos (name, default_price, etc.) cuando existan en la
    // migración y agrégalos también a $fillable.
    protected $fillable = [];

    /**
     * Indicaciones adicionales aplicadas a órdenes completas
     * (serviceable_type = App\Models\Order).
     */
    public function orders(): MorphToMany
    {
        return $this->morphedByMany(Order::class, 'serviceable')
            ->withPivot(['price', 'notes'])
            ->withTimestamps()
            ->using(Serviceable::class);
    }

    /**
     * Indicaciones adicionales aplicadas a líneas individuales
     * de order_photo_type (serviceable_type = App\Models\OrderPhotoType).
     */
    public function orderPhotoTypes(): MorphToMany
    {
        return $this->morphedByMany(OrderPhotoType::class, 'serviceable')
            ->withPivot(['price', 'notes'])
            ->withTimestamps()
            ->using(Serviceable::class);
    }
}