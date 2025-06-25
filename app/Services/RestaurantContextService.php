<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\EmbeddingService;  // IMPORTACIÓN CORRECTA

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
            try {
                match ($intent) {
                    'search_restaurant' => $context['restaurants'] = $this->searchRestaurants($message),
                    'category_info'     => $context['categories']   = $this->getCategories(),
                    'recommendations'   => $context['popular']       = $this->getPopularRestaurants(),
                    'reviews'           => $context['reviews']       = $this->getRecentReviews(),
                    default             => null,
                };
            } catch (\Throwable $e) {
                Log::warning("ContextService error [$intent]: " . $e->getMessage());
            }
        }

        return array_filter($context);
    }

    /**
     * Detecta las intenciones del usuario basándose en palabras clave.
     */
    private function detectIntentions(string $message): array
    {
        $msg = strtolower($message);
        $map = [
            'search_restaurant' => ['buscar', 'restaurante', 'comida', 'quiero'],
            'category_info'     => ['categoria', 'tipo', 'italiana', 'mexicana'],
            'recommendations'   => ['popular', 'mejor', 'top'],
            'reviews'           => ['reseña', 'opinion', 'review'],
        ];

        foreach ($map as $intent => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($msg, $kw)) {
                    return [$intent];
                }
            }
        }

        return ['search_restaurant'];
    }

    /**
     * Busca restaurantes: primero por embeddings, luego por LIKE en términos clave.
     */
    private function searchRestaurants(string $message): array
    {
        return Cache::remember('search_' . md5($message), 300, function () use ($message) {
            // 1) Intentar embeddings
            try {
                $emb = $this->embeddingService->findSimilarRestaurants($message, 5);
                if (!empty($emb)) {
                    return $emb;
                }
            } catch (\Throwable $e) {
                Log::warning("Embeddings failed: " . $e->getMessage());
            }

            // 2) Fallback con términos clave
            $terms = $this->extractSearchTerms($message);
            if (empty($terms)) {
                return [];
            }

            $query = Restaurant::query();
            foreach ($terms as $term) {
                if (strlen($term) < 3) {
                    continue;
                }

                $query->orWhere('name', 'LIKE', "%{$term}%")
                      ->orWhere('description', 'LIKE', "%{$term}%")
                      ->orWhere('address', 'LIKE', "%{$term}%")
                      ->orWhereHas('categories', function ($q) use ($term) {
                          $q->where('name', 'LIKE', "%{$term}%");
                      });
            }

            return $query->with('categories')
                         ->limit(5)
                         ->get()
                         ->map(function ($r) {
                             return [
                                 'id'             => $r->id,
                                 'name'           => $r->name,
                                 'description'    => $r->description,
                                 'address'        => $r->address,
                                 'phone'          => $r->phone,
                                 'email'          => $r->email,
                                 'categories'     => $r->categories->pluck('name')->toArray(),
                                 'average_rating' => $r->reviews()->avg('rating') ?? 0,
                             ];
                         })
                         ->toArray();
        });
    }

    /**
     * Elimina puntuación y stop-words, devuelve términos relevantes.
     */
    private function extractSearchTerms(string $message): array
    {
        // Quita signos de puntuación y pasa a minúsculas
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($message));
        $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);

        $stop = ['el','la','de','y','en','un','una','para','con','restaurante','lugar'];
        return array_values(array_filter($words, function ($w) use ($stop) {
            return strlen($w) >= 3 && !in_array($w, $stop);
        }));
    }

    /**
     * Lista categorías con contador de restaurantes.
     */
    private function getCategories(): array
    {
        return Cache::remember('cats', 1440, function () {
            return Category::withCount('restaurants')
                ->get()
                ->map(function ($c) {
                    return [
                        'id'    => $c->id,
                        'name'  => $c->name,
                        'count' => $c->restaurants_count,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Devuelve restaurantes más populares por valoración.
     */
    private function getPopularRestaurants(): array
    {
        return Cache::remember('popular', 720, function () {
            return Restaurant::leftJoin('reviews', 'restaurants.id', '=', 'reviews.restaurant_id')
                ->selectRaw('restaurants.*, AVG(reviews.rating) as avg_rating')
                ->groupBy('restaurants.id')
                ->orderByDesc('avg_rating')
                ->limit(5)
                ->with('categories')
                ->get()
                ->map(function ($r) {
                    return [
                        'id'         => $r->id,
                        'name'       => $r->name,
                        'rating'     => round($r->avg_rating, 2),
                        'categories' => $r->categories->pluck('name')->toArray(),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Obtiene las últimas 5 reseñas.
     */
    private function getRecentReviews(): array
    {
        return Cache::remember('reviews', 360, function () {
            return Review::with(['restaurant', 'user'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(function ($rev) {
                    return [
                        'user'    => $rev->user->name ?? 'Anónimo',
                        'rating'  => $rev->rating,
                        'comment' => $rev->comment,
                        'place'   => $rev->restaurant->name,
                    ];
                })
                ->toArray();
        });
    }
}
