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

    protected $appends = [
        'women_features_text',
        'men_features_text',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function photoTypes(): BelongsToMany
    {
        return $this->belongsToMany(PhotoType::class, 'package_photo_types')
            ->withPivot('quantity')
            ->withTimestamps()
            ->using(PackagePhotoType::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_packages')
            ->withPivot(['package_name_snapshot', 'price_snapshot'])
            ->withTimestamps()
            ->using(OrderPackage::class);
    }

    public function getWomenFeaturesTextAttribute(): string
    {
        return $this->women_features['text'] ?? '';
    }

    public function getMenFeaturesTextAttribute(): string
    {
        return $this->men_features['text'] ?? '';
    }
}