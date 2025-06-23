// app/Services/RestaurantContextService.php
<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Review;

class RestaurantContextService
{
    public function getRelevantContext(string $userMessage): array
    {
        $context = [];
        
        // Detectar intenciones en el mensaje
        $intentions = $this->detectIntentions($userMessage);
        
        foreach ($intentions as $intention) {
            match($intention) {
                'search_restaurant' => $context['restaurants'] = $this->searchRestaurants($userMessage),
                'category_info' => $context['categories'] = $this->getCategories(),
                'recommendations' => $context['popular'] = $this->getPopularRestaurants(),
                'reviews' => $context['reviews'] = $this->getRecentReviews(),
                default => null
            };
        }
        
        return $context;
    }
    
    private function detectIntentions(string $message): array
    {
        $intentions = [];
        $message = strtolower($message);
        
        $patterns = [
            'search_restaurant' => ['buscar', 'encontrar', 'recomendar', 'restaurante', 'comida'],
            'category_info' => ['categoría', 'tipo', 'cocina', 'estilo'],
            'recommendations' => ['recomendación', 'sugerir', 'mejor', 'popular'],
            'reviews' => ['opinión', 'review', 'comentario', 'valoración']
        ];
        
        foreach ($patterns as $intention => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    $intentions[] = $intention;
                    break;
                }
            }
        }
        
        return array_unique($intentions);
    }
    
    private function searchRestaurants(string $query): array
    {
        return Restaurant::where('name', 'LIKE', "%{$query}%")
            ->orWhere('address', 'LIKE', "%{$query}%")
            ->with(['categories', 'reviews'])
            ->limit(5)
            ->get()
            ->toArray();
    }
    
    private function getCategories(): array
    {
        return Category::withCount('restaurants')->get()->toArray();
    }
    
    private function getPopularRestaurants(): array
    {
        return Restaurant::withAvg('reviews', 'rating')
            ->orderBy('reviews_avg_rating', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }
    
    private function getRecentReviews(): array
    {
        return Review::with(['restaurant', 'user'])
            ->latest()
            ->limit(10)
            ->get()
            ->toArray();
    }

// En RestaurantContextService
private function getPopularRestaurants(): array
{
    return Cache::remember('popular_restaurants', 3600, function() {
        return Restaurant::withAvg('reviews', 'rating')
            ->orderBy('reviews_avg_rating', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    });
}



}
