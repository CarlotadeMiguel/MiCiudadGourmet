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
        Log::info("RestaurantContextService: Procesando mensaje", ['message' => $message]);

        $intents = $this->detectIntentions($message);
        Log::info("RestaurantContextService: Intenciones detectadas", ['intents' => $intents]);

        $context = [];

        foreach ($intents as $intent) {
            try {
                match ($intent) {
                    'search_restaurant' => $context['restaurants'] = $this->searchRestaurants($message),
                    'category_info'     => $context['categories']  = $this->getCategories(),
                    'recommendations'   => $context['popular']     = $this->getPopularRestaurants(),
                    'reviews'           => $context['reviews']     = $this->getRecentReviews(),
                    default             => null,
                };
            } catch (\Exception $e) {
                Log::error("RestaurantContextService: Error procesando intención {$intent}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        $filtered = array_filter($context);
        Log::info("RestaurantContextService: Contexto generado", ['context' => $filtered]);

        return $filtered;
    }

    /**
     * Detecta las intenciones del usuario basándose en palabras clave y patrones.
     */
    private function detectIntentions(string $message): array
    {
        $message = strtolower($message);
        $intents = [];

        $searchKeywords = ['buscar','busco','encontrar','recomendar','sugerir','mostrar','restaurante','comida','comer','cenar','almorzar','cerca','mejor','bueno','donde','quiero'];
        $categoryKeywords = ['tipo','categoria','categoría','clase','estilo','italiana','mexicana','china','japonesa','española','pizza','sushi','taco','hamburguesa','pasta'];
        $recommendationKeywords = ['popular','mejor','mejores','recomendado','top','favorito','valorado','puntuado','destacado'];
        $reviewKeywords = ['opinion','opinión','reseña','comentario','valoracion','valoración','puntuacion','puntuación','review'];

        foreach ($searchKeywords as $kw) {
            if (str_contains($message, $kw)) {
                $intents[] = 'search_restaurant';
                break;
            }
        }
        foreach ($categoryKeywords as $kw) {
            if (str_contains($message, $kw)) {
                $intents[] = 'category_info';
                break;
            }
        }
        foreach ($recommendationKeywords as $kw) {
            if (str_contains($message, $kw)) {
                $intents[] = 'recommendations';
                break;
            }
        }
        foreach ($reviewKeywords as $kw) {
            if (str_contains($message, $kw)) {
                $intents[] = 'reviews';
                break;
            }
        }

        if (empty($intents)) {
            $intents[] = 'search_restaurant';
        }

        return array_unique($intents);
    }

    /**
     * Busca restaurantes usando embeddings y, en fallback, términos clave.
     */
    private function searchRestaurants(string $message): array
    {
        return Cache::remember("search_restaurants_" . md5($message), 300, function () use ($message) {
            // 1. Intentar embeddings
            try {
                $embeds = $this->embeddingService->findSimilarRestaurants($message, 5);
                if (!empty($embeds)) {
                    return $embeds;
                }
            } catch (\Exception $e) {
                Log::warning("Embeddings failed: " . $e->getMessage());
            }

            // 2. Fallback: búsqueda por términos clave
            $terms = $this->extractSearchTerms($message);
            if (empty($terms)) {
                return [];
            }

            $query = Restaurant::query();
            foreach ($terms as $term) {
                if (strlen($term) >= 3) {
                    $query->orWhere('name',        'LIKE', "%{$term}%")
                          ->orWhere('description', 'LIKE', "%{$term}%")
                          ->orWhere('address',     'LIKE', "%{$term}%")
                          ->orWhereHas('categories', fn($q) => $q->where('name','LIKE',"%{$term}%"));
                }
            }

            return $query->with('categories')
                         ->limit(5)
                         ->get()
                         ->map(fn($r) => [
                             'id'             => $r->id,
                             'name'           => $r->name,
                             'description'    => $r->description,
                             'address'        => $r->address,
                             'phone'          => $r->phone,
                             'email'          => $r->email,
                             'image'          => $r->image,
                             'categories'     => $r->categories->pluck('name')->toArray(),
                             'average_rating' => $r->reviews()->avg('rating') ?? 0,
                         ])->toArray();
        });
    }

    /**
     * Extrae términos relevantes del mensaje para búsquedas parciales.
     */
    private function extractSearchTerms(string $message): array
    {
        $stopWords = ['el','la','de','y','en','un','una','me','te','para','con','restaurante','restaurantes','lugar'];
        $words = preg_split('/\s+/', strtolower($message));
        return array_values(array_filter($words, fn($w) =>
            strlen($w) >= 3 && !in_array($w, $stopWords)
        ));
    }

    /**
     * Obtiene todas las categorías con contador de restaurantes.
     */
    private function getCategories(): array
    {
        return Cache::remember('restaurant_categories', 1440, function () {
            return Category::withCount('restaurants')
                ->get()
                ->map(fn($cat) => [
                    'id'                => $cat->id,
                    'name'              => $cat->name,
                    'description'       => $cat->description,
                    'restaurants_count' => $cat->restaurants_count,
                ])->toArray();
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
                ->leftJoin('reviews','restaurants.id','=','reviews.restaurant_id')
                ->groupBy('restaurants.id')
                ->having('review_count','>=',1)
                ->orderBy('average_rating','desc')
                ->orderBy('review_count','desc')
                ->with('categories')
                ->limit(5)
                ->get()
                ->map(fn($r) => [
                    'id'             => $r->id,
                    'name'           => $r->name,
                    'description'    => $r->description,
                    'address'        => $r->address,
                    'phone'          => $r->phone,
                    'email'          => $r->email,
                    'image'          => $r->image,
                    'categories'     => $r->categories->pluck('name')->toArray(),
                    'average_rating' => round($r->average_rating, 2),
                    'review_count'   => $r->review_count,
                ])->toArray();
        });
    }

    /**
     * Obtiene las reseñas más recientes.
     */
    private function getRecentReviews(): array
    {
        return Cache::remember('recent_reviews', 360, function () {
            return Review::with(['restaurant','user'])
                ->orderBy('created_at','desc')
                ->limit(10)
                ->get()
                ->map(fn($rev) => [
                    'id'         => $rev->id,
                    'rating'     => $rev->rating,
                    'comment'    => $rev->comment,
                    'created_at' => $rev->created_at->format('d/m/Y H:i'),
                    'restaurant' => [
                        'id'   => $rev->restaurant->id,
                        'name' => $rev->restaurant->name,
                    ],
                    'user'       => [
                        'name' => $rev->user->name ?? 'Usuario anónimo',
                    ],
                ])->toArray();
        });
    }
}
