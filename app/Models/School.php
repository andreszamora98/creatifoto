<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'sequence',
        'name',
    ];

    protected $casts = [
        'sequence' => 'integer',
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }
}