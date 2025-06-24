<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\RestaurantEmbedding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Retorna embedding del restaurante con cache.
     */
    public function createRestaurantEmbedding(Restaurant $restaurant): array
    {
        return Cache::remember("emb_{$restaurant->id}", 1440, function () use ($restaurant) {
            $text = $this->buildText($restaurant);
            $res  = Http::post("{$this->apiUrl}?key={$this->apiKey}", [
                'model'   => 'models/text-embedding-004',
                'content' => ['parts'=>[['text'=>$text]]]
            ]);

            if (!$res->successful()) {
                Log::error("Error embedding: ".$res->body());
                throw new \Exception("Embedding failed");
            }

            $values = $res->json()['embedding']['values'];
            RestaurantEmbedding::updateOrCreate(
                ['restaurant_id'=>$restaurant->id],
                ['embedding'=>$values, 'text_content'=>$text]
            );
            return $values;
        });
    }

    /**
     * Busca restaurantes similares por similitud coseno.
     */
    public function findSimilarRestaurants(string $query, int $limit=5): array
    {
        $queryEmb = $this->generateEmbedding($query);
        // Lógica de cosineSimilarity y retorno de resultados con detalles.
    }

    // Métodos auxiliares: buildText, generateEmbedding, cosineSimilarity...
}
