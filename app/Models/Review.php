<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'rating',
        'comment',
        'user_id',
        'restaurant_id'
    ];

    // Relación: Una reseña pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Una reseña pertenece a un restaurante
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
