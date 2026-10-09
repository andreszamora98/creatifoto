<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaperType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'extra_price',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
    ];

    /** Líneas históricas que usan este papel (paper_type_id es restrictOnDelete). */
    public function orderPhotoTypes(): HasMany
    {
        return $this->hasMany(OrderPhotoType::class);
    }
}