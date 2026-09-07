<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => trim(preg_replace('/\s+/', ' ', $value)),
        );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}