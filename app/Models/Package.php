<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'sequence',
        'name',
        'total_price',
        'women_features',
        'men_features',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'total_price' => 'decimal:2',
        'women_features' => 'array',
        'men_features' => 'array',
    ];

    protected $appends = [
        'package_number',
        'women_features_text',
        'men_features_text',
    ];

    protected $hidden = [
        'sequence',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class)->withTrashed();
    }

    public function photoTypes(): BelongsToMany
    {
        return $this->belongsToMany(PhotoType::class, 'package_photo_types')
            ->withPivot('quantity')
            ->withTimestamps()
            ->using(PackagePhotoType::class)
            ->withTrashed();
    }

    public function orderPackages(): HasMany
    {
        return $this->hasMany(OrderPackage::class);
    }

    public function quotePackages(): HasMany
    {
        return $this->hasMany(QuotePackage::class);
    }

    public function getPackageNumberAttribute(): int
    {
        return (int) $this->sequence;
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