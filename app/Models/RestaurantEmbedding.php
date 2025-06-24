<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantEmbedding extends Model
{
    protected $fillable = [
        'restaurant_id',
        'embedding',
        'text_content'
    ];

    protected $casts = [
        'embedding' => 'array'
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
