<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'package_number',
        'name',
        'total_price',
        'women_features',
        'men_features',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'women_features' => 'array',
        'men_features' => 'array',
    ];

    /**
     * package_id es cascadeOnDelete: si la escuela muere, sus paquetes
     * mueren con ella.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * La "plantilla" del paquete: qué tipos de foto trae y en qué
     * cantidad. package_id es cascadeOnDelete, photo_type_id es
     * restrictOnDelete (protege el catálogo).
     */
    public function photoTypes(): BelongsToMany
    {
        return $this->belongsToMany(PhotoType::class, 'package_photo_type')
            ->withPivot('quantity')
            ->withTimestamps()
            ->using(PackagePhotoType::class);
    }

    /**
     * Órdenes que han vendido este paquete. package_id es
     * restrictOnDelete aquí a propósito: un paquete ya vendido no se
     * debe poder borrar sin romper el historial. Para retirarlo de
     * oferta se usa soft-delete, no un delete físico.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_package')
            ->withPivot(['package_name_snapshot', 'price_snapshot'])
            ->withTimestamps()
            ->using(OrderPackage::class);
    }
}