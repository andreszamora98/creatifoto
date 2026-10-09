<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdditionalService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'default_price',
        'default_quantity',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'default_quantity' => 'integer',
    ];

    /**
     * Usos históricos del servicio (órdenes, cotizaciones). Reemplaza los tres morphedByMany
     * que dependían del pivote; para filtrar por tipo: ->where('serviceable_type', 'order').
     */
    public function serviceables(): HasMany
    {
        return $this->hasMany(Serviceable::class);
    }
}