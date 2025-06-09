<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{   
    use HasFactory;
    protected $fillable = [
        'name'
    ];

    // Relación: Una categoría puede tener muchos restaurantes (muchos a muchos)
    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class);
    }
}
