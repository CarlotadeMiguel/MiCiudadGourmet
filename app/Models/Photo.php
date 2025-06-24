<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Photo extends Model
{
    use HasFactory;
    protected $fillable = [
        'url',
        'imageable_id',
        'imageable_type'
    ];

    // Relación polimórfica: una foto puede pertenecer a un restaurante o a una reseña
    public function imageable()
    {
        return $this->morphTo();
    }
}
