<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // $fillable: campos que pueden asignarse masivamente
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // Relación: Un usuario puede tener muchos restaurantes
    public function restaurants()
    {
        // hasMany: Un usuario tiene muchos restaurantes
        return $this->hasMany(Restaurant::class);
    }

    // Relación: Un usuario puede tener muchos favoritos (muchos a muchos)
    public function favorites()
    {
        // belongsToMany: Un usuario puede marcar muchos restaurantes como favoritos
        return $this->belongsToMany(Restaurant::class, 'favorites');
    }

    // Relación: Un usuario puede hacer muchas reseñas
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
