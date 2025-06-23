<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Review;
use App\Services\EmbeddingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RestaurantContextService
{
    private EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Obtiene el contexto relevante basado en el mensaje del usuario
     */
    public function getRelevantContext(string $userMessage): array
    {
        $context = [];
        
        // Detectar intenciones en el mensaje
        $intentions = $this->detectIntentions($userMessage);
        
        foreach ($intentions as $intention) {
            match($intention) {
                'search_restaurant' => $context['restaurants'] = $this->searchRestaurantsWithEmbeddings($userMessage),
                'category_info' => $context['categories'] = $this->getCategories(),
                'recommendations' => $context['popular'] = $this->getPopularRestaurants(),
                'reviews' => $context['reviews'] = $this->getRecentReviews(),
                'location_based' => $context['nearby'] = $this->getNearbyRestaurants($userMessage),
                default => null
            };
        }
        
        return array_filter($context); // Eliminar valores null
    }

    /**
     * Busca restaurantes usando embeddings vectoriales con fallback
     */
    private function searchRestaurantsWithEmbeddings(string $query): array
    {
        try {
            // Intentar búsqueda semántica con embeddings
            $semanticResults = $this->embeddingService->findSimilarRestaurants($query, 5);
            
            if (!empty($semanticResults)) {
                return array_map(function($result) {
                    return [
                        'id' => $result['restaurant']->id,
                        'name' => $result['restaurant']->name,
                        'address' => $result['restaurant']->address,
                        'description' => $result['restaurant']->description,
                        'phone' => $result['restaurant']->phone,
                        'categories' => $result['restaurant']->categories->pluck('name')->toArray(),
                        'average_rating' => $result['restaurant']->reviews->avg('rating'),
                        'reviews_count' => $result['restaurant']->reviews->count(),
                        'similarity_score' => round($result['similarity'], 3),
                        'match_reason' => 'Búsqueda semántica',
                        'restaurant_data' => $result['restaurant']->toArray()
                    ];
                }, $semanticResults);
            }
            
            Log::info('Búsqueda semántica sin resultados, usando fallback tradicional');
            
        } catch (\Exception $e) {
            Log::error('Error en búsqueda semántica: ' . $e->getMessage());
        }

        // Fallback a búsqueda tradicional si falla la semántica
        return $this->searchRestaurantsTraditional($query);
    }

    /**
     * Búsqueda tradicional como fallback
     */
    private function searchRestaurantsTraditional(string $query): array
    {
        $restaurants = Restaurant::where(function($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('address', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->with(['categories', 'reviews'])
            ->limit(5)
            ->get();

        return $restaurants->map(function($restaurant) {
            return [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'address' => $restaurant->address,
                'description' => $restaurant->description,
                'phone' => $restaurant->phone,
                'categories' => $restaurant->categories->pluck('name')->toArray(),
                'average_rating' => $restaurant->reviews->avg('rating'),
                'reviews_count' => $restaurant->reviews->count(),
                'similarity_score' => null,
                'match_reason' => 'Búsqueda tradicional',
                'restaurant_data' => $restaurant->toArray()
            ];
        })->toArray();
    }

    /**
     * Busca restaurantes cercanos basado en ubicación mencionada
     */
    private function getNearbyRestaurants(string $query): array
    {
        // Extraer posibles ubicaciones del query
        $locations = $this->extractLocations($query);
        
        if (empty($locations)) {
            return [];
        }

        $restaurants = Restaurant::where(function($queryBuilder) use ($locations) {
                foreach ($locations as $location) {
                    $queryBuilder->orWhere('address', 'LIKE', "%{$location}%");
                }
            })
            ->with(['categories', 'reviews'])
            ->limit(8)
            ->get();

        return $restaurants->map(function($restaurant) {
            return [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'address' => $restaurant->address,
                'categories' => $restaurant->categories->pluck('name')->toArray(),
                'average_rating' => round($restaurant->reviews->avg('rating') ?? 0, 1),
                'reviews_count' => $restaurant->reviews->count()
            ];
        })->toArray();
    }

    /**
     * Extrae ubicaciones mencionadas en el query
     */
    private function extractLocations(string $query): array
    {
        $query = strtolower($query);
        $commonLocations = [
            'centro', 'centro histórico', 'casco antiguo', 'downtown',
            'norte', 'sur', 'este', 'oeste',
            'plaza', 'avenida', 'calle', 'barrio',
            'estación', 'metro', 'tren', 'autobús'
        ];

        $foundLocations = [];
        foreach ($commonLocations as $location) {
            if (str_contains($query, $location)) {
                $foundLocations[] = $location;
            }
        }

        return $foundLocations;
    }

    /**
     * Detecta las intenciones del usuario en el mensaje
     */
    private function detectIntentions(string $message): array
    {
        $intentions = [];
        $message = strtolower($message);
        
        $patterns = [
            'search_restaurant' => [
                'buscar', 'encontrar', 'recomendar', 'restaurante', 'comida', 
                'comer', 'cenar', 'almorzar', 'desayunar', 'quiero', 'necesito'
            ],
            'category_info' => [
                'categoría', 'tipo', 'cocina', 'estilo', 'italiana', 'mexicana', 
                'japonesa', 'china', 'española', 'francesa', 'árabe', 'vegetariana', 
                'vegana', 'pizza', 'pasta', 'sushi', 'tacos', 'hamburguesa'
            ],
            'recommendations' => [
                'recomendación', 'sugerir', 'mejor', 'popular', 'top', 'bueno', 
                'excelente', 'destacado', 'favorito', 'imperdible'
            ],
            'reviews' => [
                'opinión', 'review', 'comentario', 'valoración', 'calificación', 
                'estrella', 'experiencia', 'qué tal', 'cómo está'
            ],
            'location_based' => [
                'cerca', 'cercano', 'próximo', 'donde', 'ubicación', 'dirección',
                'centro', 'barrio', 'zona', 'área', 'alrededor'
            ]
        ];
        
        foreach ($patterns as $intention => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    $intentions[] = $intention;
                    break; // Evitar duplicados por intención
                }
            }
        }
        
        return array_unique($intentions);
    }

    /**
     * Obtiene todas las categorías con conteo de restaurantes
     */
    private function getCategories(): array
    {
        return Cache::remember('restaurant_categories', 1800, function() {
            return Category::withCount('restaurants')
                ->orderBy('restaurants_count', 'desc')
                ->get()
                ->map(function($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description ?? '',
                        'restaurants_count' => $category->restaurants_count
                    ];
                })
                ->toArray();
        });
    }
    
    /**
     * Obtiene los restaurantes más populares basado en calificaciones
     */
    private function getPopularRestaurants(): array
    {
        return Cache::remember('popular_restaurants', 3600, function() {
            return Restaurant::withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->having('reviews_count', '>=', 3) // Mínimo 3 reseñas para ser considerado
                ->orderBy('reviews_avg_rating', 'desc')
                ->orderBy('reviews_count', 'desc')
                ->limit(8)
                ->get()
                ->map(function($restaurant) {
                    return [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'address' => $restaurant->address,
                        'description' => $restaurant->description,
                        'average_rating' => round($restaurant->reviews_avg_rating, 1),
                        'reviews_count' => $restaurant->reviews_count,
                        'categories' => $restaurant->categories->pluck('name')->toArray()
                    ];
                })
                ->toArray();
        });
    }
    
    /**
     * Obtiene las reseñas más recientes con información completa
     */
    private function getRecentReviews(): array
    {
        return Cache::remember('recent_reviews', 900, function() {
            return Review::with(['restaurant', 'user'])
                ->whereNotNull('comment')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at->format('d/m/Y'),
                        'restaurant' => [
                            'id' => $review->restaurant->id,
                            'name' => $review->restaurant->name,
                            'address' => $review->restaurant->address
                        ],
                        'user' => [
                            'name' => $review->user->name ?? 'Usuario anónimo'
                        ]
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Limpia el caché de contexto cuando sea necesario
     */
    public function clearContextCache(): void
    {
        Cache::forget('restaurant_categories');
        Cache::forget('popular_restaurants');
        Cache::forget('recent_reviews');
        
        Log::info('Caché de contexto de restaurantes limpiado');
    }

    /**
     * Obtiene estadísticas generales para el contexto
     */
    public function getGeneralStats(): array
    {
        return Cache::remember('restaurant_general_stats', 7200, function() {
            return [
                'total_restaurants' => Restaurant::count(),
                'total_categories' => Category::count(),
                'total_reviews' => Review::count(),
                'average_rating' => round(Review::avg('rating'), 1),
                'restaurants_with_reviews' => Restaurant::has('reviews')->count()
            ];
        });
    }
}
