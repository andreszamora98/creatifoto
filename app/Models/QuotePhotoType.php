<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotePhotoType extends Model
{
    protected $fillable = [
        'quote_id',
        'photo_type_id',
        'quantity',
        'unit_price',
        'format',
        'paper_type_id',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function photoType(): BelongsTo
    {
        return $this->belongsTo(PhotoType::class);
    }

    public function paperType(): BelongsTo
    {
        return $this->belongsTo(PaperType::class);
    }
}