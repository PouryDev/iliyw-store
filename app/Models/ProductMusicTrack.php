<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMusicTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'title',
        'artist',
        'file_path',
        'duration',
        'sort_order',
    ];

    protected $casts = [
        'duration' => 'integer',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) {
            return '0:00';
        }
        
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
