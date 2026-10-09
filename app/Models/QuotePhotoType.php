<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotePhotoType extends Model
{
    protected $fillable = [
        'quote_id',
        'quote_package_id',
        'photo_type_id',
        'photo_type_name_snapshot',
        'quantity',
        'unit_price',
        'format',
        'paper_type_id',
        'paper_type_name_snapshot',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function quotePackage(): BelongsTo
    {
        return $this->belongsTo(QuotePackage::class);
    }

    public function photoType(): BelongsTo
    {
        return $this->belongsTo(PhotoType::class)->withTrashed();
    }

    public function paperType(): BelongsTo
    {
        return $this->belongsTo(PaperType::class)->withTrashed();
    }
}