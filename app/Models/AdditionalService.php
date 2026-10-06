<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class AdditionalService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'default_price',
        'default_quantity',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'default_quantity' => 'integer',
    ];

    public function orders(): MorphToMany
    {
        return $this->morphedByMany(Order::class, 'serviceable')
            ->withPivot(['price', 'notes'])
            ->withTimestamps()
            ->using(Serviceable::class);
    }

    public function orderPhotoTypes(): MorphToMany
    {
        return $this->morphedByMany(OrderPhotoType::class, 'serviceable')
            ->withPivot(['price', 'notes'])
            ->withTimestamps()
            ->using(Serviceable::class);
    }
    public function quotes(): MorphToMany
    {
        return $this->morphedByMany(Quote::class, 'serviceable')
            ->withPivot(['price', 'notes'])
            ->withTimestamps()
            ->using(Serviceable::class);
    }
}