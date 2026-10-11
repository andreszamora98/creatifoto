<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function serviceables(): HasMany
    {
        return $this->hasMany(Serviceable::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_additional_services')
            ->withPivot(['quantity', 'unit_price'])
            ->withTimestamps()
            ->using(PackageAdditionalService::class);
    }
}