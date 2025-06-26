<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Restaurant;
use App\Models\RestaurantEmbedding;
use App\Services\EmbeddingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        if (Schema::hasTable('restaurant_embeddings')) {
        if (RestaurantEmbedding::count() === 0) {
            Restaurant::chunk(100, fn($chunk) => 
                collect($chunk)->each(fn($r)=>
                    app(EmbeddingService::class)->createRestaurantEmbedding($r)
                )
            );
        }
    }
}
}
