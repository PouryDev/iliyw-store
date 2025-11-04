<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'purpose',
        'type',
        'disk',
        'path',
        'size',
        'mime',
        'original_name',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}


