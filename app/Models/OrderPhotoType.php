<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPhotoType extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_package_id',
        'photo_type_id',
        'photo_type_name_snapshot',
        'quantity',
        'unit_price',
        'format',
        'background',
        'paper_type_id',
        'paper_type_name_snapshot',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderPackage(): BelongsTo
    {
        return $this->belongsTo(OrderPackage::class);
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