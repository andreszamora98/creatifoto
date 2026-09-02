<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Cada escuela puede ofrecer varios paquetes.
     * packages.school_id es cascadeOnDelete: si se borra la escuela,
     * sus paquetes desaparecen con ella.
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }
}