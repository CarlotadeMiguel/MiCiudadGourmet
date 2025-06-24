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

    // Métodos privados: detectIntentions, searchRestaurants, getCategories, getPopularRestaurants, getRecentReviews
    // Incluyen lógica de embeddings, fallback tradicional y cacheado para optimizar rendimiento.
}
