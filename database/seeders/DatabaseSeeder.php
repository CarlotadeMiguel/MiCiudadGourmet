<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Review;
use App\Models\Photo;
use App\Models\RestaurantEmbedding;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear 10 usuarios
        $users = User::factory(10)->create();

        // 2. Crear 5 categorías
        $categories = Category::factory(5)->create();

        // 3. Crear 20 restaurantes, cada uno asociado a un usuario existente y varias categorías
        $restaurants = Restaurant::factory(20)->make()->each(function ($restaurant) use ($users, $categories) {
            // Asignar usuario aleatorio como propietario
            $restaurant->user_id = $users->random()->id;
            $restaurant->save();

            // Asociar de 1 a 3 categorías aleatorias
            $restaurant->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Crear 1-2 fotos polimórficas para el restaurante
            Photo::factory(rand(1, 2))->create([
                'imageable_id' => $restaurant->id,
                'imageable_type' => Restaurant::class,
            ]);

            RestaurantEmbedding::create([
                'restaurant_id' => $restaurant->id,
                'embedding' => array_map(fn() => round(mt_rand() / mt_getrandmax(), 4), range(1, 10)), // vector de 10 floats
                'text_content' => "{$restaurant->name}. {$restaurant->description}",
            ]);
        });

        // 4. Crear 50 reseñas, cada una asociada a un usuario y a un restaurante
        for ($i = 0; $i < 50; $i++) {
            Review::factory()->create([
                'user_id' => $users->random()->id,
                'restaurant_id' => $restaurants->random()->id,
            ]);
        }

        // 5. Crear favoritos aleatorios (cada usuario marca de 1 a 5 restaurantes como favoritos)
        foreach ($users as $user) {
            $user->favorites()->attach(
                $restaurants->random(rand(1, 5))->pluck('id')->toArray()
            );
        }
    }
}
