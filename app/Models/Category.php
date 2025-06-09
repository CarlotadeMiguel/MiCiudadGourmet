<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name'
    ];

    // Relación: Una categoría puede tener muchos restaurantes (muchos a muchos)
    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class);
    }
}
