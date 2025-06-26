<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\EmbeddingService;

class Restaurant extends Model
{
    use HasFactory;
    // $fillable: campos que pueden asignarse masivamente
    protected $fillable = [
        'name',
        'address',
        'phone',
        'description',
        'user_id'
    ];

    // Relación: Un restaurante pertenece a un usuario
    public function user()
    {
        // belongsTo: Un restaurante pertenece a un usuario
        return $this->belongsTo(User::class);
    }

    // Relación: Un restaurante puede tener muchas reseñas
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Relación: Un restaurante puede tener muchas fotos (polimórfica)
    public function photos()
    {
        // morphMany: Un restaurante puede tener muchas fotos
        return $this->morphMany(Photo::class, 'imageable');
    }

    // Relación: Un restaurante puede tener muchas categorías (muchos a muchos)
    public function categories()
    {
        // belongsToMany: Relación muchos a muchos con categorías
        return $this->belongsToMany(Category::class);
    }

    // Relación: Un restaurante puede ser favorito de muchos usuarios
    public function favoredBy()
    {
        // belongsToMany: Relación inversa de favoritos
        return $this->belongsToMany(User::class, 'favorites');
    }

    // Ejemplo de scope: solo restaurantes activos
    public function scopeActive($query)
    {
        // Filtra solo restaurantes con status = 'active'
        return $query->where('status', 'active');
    }

    // Ejemplo de accessor: capitaliza el nombre
    public function getNameAttribute($value)
    {
        return ucwords($value);
    }

public function embedding()
{
    return $this->hasOne(RestaurantEmbedding::class);
}

// En el modelo Restaurant
protected static function booted()
{
    static::created(function ($restaurant) {
        dispatch(function () use ($restaurant) {
            app(EmbeddingService::class)->createRestaurantEmbedding($restaurant);
        })->afterResponse();
    });

    static::updated(function ($restaurant) {
        dispatch(function () use ($restaurant) {
            app(EmbeddingService::class)->createRestaurantEmbedding($restaurant);
        })->afterResponse();
    });
}


}
