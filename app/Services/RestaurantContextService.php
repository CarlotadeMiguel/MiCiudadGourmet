<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RestaurantContextService
{
    private EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Devuelve contexto relevante según intenciones detectadas.
     */
    public function getRelevantContext(string $message): array
    {
        $intents = $this->detectIntentions($message);
        $context = [];

        foreach ($intents as $intent) {
            match ($intent) {
                'search_restaurant' => $context['restaurants'] = $this->searchRestaurants($message),
                'category_info'     => $context['categories']  = $this->getCategories(),
                'recommendations'   => $context['popular']     = $this->getPopularRestaurants(),
                'reviews'           => $context['reviews']     = $this->getRecentReviews(),
                default             => null,
            };
        }

        return array_filter($context);
    }

    /**
     * Detecta las intenciones del usuario basándose en palabras clave y patrones.
     */
    private function detectIntentions(string $message): array
    {
        $message = strtolower($message);
        $intents = [];

        // Palabras clave para búsqueda de restaurantes
        $searchKeywords = [
            'buscar', 'encontrar', 'recomendar', 'sugerir', 'mostrar',
            'restaurante', 'comida', 'comer', 'cenar', 'almorzar',
            'cerca', 'mejor', 'bueno', 'donde'
        ];

        // Palabras clave para categorías
        $categoryKeywords = [
            'tipo', 'categoria', 'categoría', 'clase', 'estilo',
            'italiana', 'mexicana', 'china', 'japonesa', 'española',
            'pizza', 'sushi', 'taco', 'hamburguesa'
        ];

        // Palabras clave para recomendaciones
        $recommendationKeywords = [
            'popular', 'mejor', 'recomendado', 'top', 'favorito',
            'valorado', 'puntuado', 'destacado'
        ];

        // Palabras clave para reseñas
        $reviewKeywords = [
            'opinion', 'opinión', 'reseña', 'comentario', 'valoracion',
            'valoración', 'puntuacion', 'puntuación', 'review'
        ];

        // Detectar intenciones basándose en palabras clave
        foreach ($searchKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $intents[] = 'search_restaurant';
                break;
            }
        }

        foreach ($categoryKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $intents[] = 'category_info';
                break;
            }
        }

        foreach ($recommendationKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $intents[] = 'recommendations';
                break;
            }
        }

        foreach ($reviewKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $intents[] = 'reviews';
                break;
            }
        }

        // Si no se detectó ninguna intención específica, usar búsqueda por defecto
        if (empty($intents)) {
            $intents[] = 'search_restaurant';
        }

        return array_unique($intents);
    }

    /**
     * Busca restaurantes usando embeddings y fallback tradicional.
     */
    private function searchRestaurants(string $message): array
    {
        return Cache::remember("search_restaurants_" . md5($message), 300, function () use ($message) {
            try {
                // Intentar usar embeddings primero
                return $this->embeddingService->findSimilarRestaurants($message, 5);
            } catch (Exception $e) {
                Log::warning("Embeddings search failed, using fallback: " . $e->getMessage());
                
                // Fallback: búsqueda tradicional por nombre y descripción
                return Restaurant::where('name', 'LIKE', "%{$message}%")
                    ->orWhere('description', 'LIKE', "%{$message}%")
                    ->orWhere('address', 'LIKE', "%{$message}%")
                    ->with(['category'])
                    ->limit(5)
                    ->get()
                    ->map(function ($restaurant) {
                        return [
                            'id' => $restaurant->id,
                            'name' => $restaurant->name,
                            'description' => $restaurant->description,
                            'address' => $restaurant->address,
                            'phone' => $restaurant->phone,
                            'email' => $restaurant->email,
                            'image' => $restaurant->image,
                            'category' => $restaurant->category->name ?? 'Sin categoría',
                            'average_rating' => $restaurant->reviews()->avg('rating') ?? 0
                        ];
                    })
                    ->toArray();
            }
        });
    }

    /**
     * Obtiene todas las categorías con contador de restaurantes.
     */
    private function getCategories(): array
    {
        return Cache::remember('restaurant_categories', 1440, function () {
            return Category::withCount('restaurants')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description,
                        'restaurants_count' => $category->restaurants_count
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Obtiene los restaurantes más populares basándose en valoraciones.
     */
    private function getPopularRestaurants(): array
    {
        return Cache::remember('popular_restaurants', 720, function () {
            return Restaurant::select([
                    'restaurants.*',
                    \DB::raw('AVG(reviews.rating) as average_rating'),
                    \DB::raw('COUNT(reviews.id) as review_count')
                ])
                ->leftJoin('reviews', 'restaurants.id', '=', 'reviews.restaurant_id')
                ->groupBy('restaurants.id')
                ->having('review_count', '>=', 1)
                ->orderBy('average_rating', 'desc')
                ->orderBy('review_count', 'desc')
                ->with(['category'])
                ->limit(5)
                ->get()
                ->map(function ($restaurant) {
                    return [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'description' => $restaurant->description,
                        'address' => $restaurant->address,
                        'phone' => $restaurant->phone,
                        'email' => $restaurant->email,
                        'image' => $restaurant->image,
                        'category' => $restaurant->category->name ?? 'Sin categoría',
                        'average_rating' => round($restaurant->average_rating, 2),
                        'review_count' => $restaurant->review_count
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Obtiene las reseñas más recientes.
     */
    private function getRecentReviews(): array
    {
        return Cache::remember('recent_reviews', 360, function () {
            return Review::with(['restaurant', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at->format('d/m/Y H:i'),
                        'restaurant' => [
                            'id' => $review->restaurant->id,
                            'name' => $review->restaurant->name
                        ],
                        'user' => [
                            'name' => $review->user->name ?? 'Usuario anónimo'
                        ]
                    ];
                })
                ->toArray();
        });
    }
}
