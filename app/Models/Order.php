<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'total',
        'pose_number',
        'notes',
        'estimated_delivery_date',
        'actual_delivery_date',
        'status',
        'signature_id',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'estimated_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
    ];

    /**
     * customer_id es cascadeOnDelete, pero Customer usa SoftDeletes,
     * así que en la práctica esto es una salvaguarda, no un flujo real.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * signature_id es nullable + nullOnDelete: si se borra la firma,
     * la orden sobrevive sin firma.
     */
    public function signature(): BelongsTo
    {
        return $this->belongsTo(Signature::class);
    }

    /**
     * Líneas del "paquete personalizado" (tipo de foto + cantidad +
     * formato + papel). order_id es cascadeOnDelete.
     */
    public function orderPhotoTypes(): HasMany
    {
        return $this->hasMany(OrderPhotoType::class);
    }

    /**
     * Paquetes escolares vendidos en esta orden. package_id es
     * restrictOnDelete (protege el historial de ventas); el paquete se
     * soft-deletea en vez de borrarse si deja de ofrecerse.
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'order_package')
            ->withPivot(['package_name_snapshot', 'price_snapshot'])
            ->withTimestamps()
            ->using(OrderPackage::class);
    }

    /**
     * Historial de abonos. order_id es cascadeOnDelete.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Indicaciones adicionales aplicadas a la orden completa (en vez
     * de a una línea individual de order_photo_type). El bloqueo mutuo
     * entre "global" y "por línea" se valida en el form request /
     * frontend, no aquí.
     */
    public function additionalServices(): MorphToMany
    {
        return $this->morphToMany(AdditionalService::class, 'serviceable')
            ->withPivot(['price', 'notes'])
            ->withTimestamps()
            ->using(Serviceable::class);
    }

    /**
     * Saldo pendiente. Nunca se guarda como columna para que no se
     * desincronice: siempre se calcula al vuelo.
     */
    public function balance(): float
    {
        return (float) $this->total - (float) $this->payments()->sum('amount');
    }
}