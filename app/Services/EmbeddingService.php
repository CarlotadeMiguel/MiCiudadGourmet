<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\RestaurantEmbedding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-exp-03-07:embedContent';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    public function createRestaurantEmbedding(Restaurant $restaurant): array
    {
        $cacheKey = "emb_{$restaurant->id}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($restaurant) {
            $text = "{$restaurant->name}. {$restaurant->description}. {$restaurant->address}";

            $values = app()->isProduction()
                ? $this->fetchEmbeddingFromAPI($text, $restaurant->id)
                : $this->generateDummyEmbedding();

            RestaurantEmbedding::updateOrCreate(
                ['restaurant_id' => $restaurant->id],
                [
                    'embedding'    => $values,
                    'text_content' => $text,
                ]
            );

            return $values;
        });
    }

    public function findSimilarRestaurants(string $query, int $limit = 5): array
    {
        $queryEmbedding = app()->isProduction()
            ? $this->createTextEmbedding($query)
            : $this->generateDummyEmbedding();

        $allEmbeddings = RestaurantEmbedding::with('restaurant')->get();

        $scores = [];
        foreach ($allEmbeddings as $embRecord) {
            $score = $this->cosineSimilarity($queryEmbedding, $embRecord->embedding);
            $scores[] = [
                'restaurant' => $embRecord->restaurant,
                'score'      => $score,
            ];
        }

        usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_map(function ($item) {
            $r = $item['restaurant'];
            return [
                'id'          => $r->id,
                'name'        => $r->name,
                'description' => $r->description,
                'address'     => $r->address,
                'image'       => $r->image,
                'categories'  => $r->categories->pluck('name')->toArray(),
            ];
        }, array_slice($scores, 0, $limit));
    }

    private function createTextEmbedding(string $text): array
    {
        return $this->fetchEmbeddingFromAPI($text);
    }

    private function fetchEmbeddingFromAPI(string $text, ?int $restaurantId = null): array
    {
        $response = Http::withBody(
            json_encode([
                'model'   => 'models/gemini-embedding-exp-03-07',
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ]),
            'application/json'
        )->post("{$this->apiUrl}?key={$this->apiKey}");

        if (!$response->successful()) {
            $id = $restaurantId ?? 'N/A';
            Log::error("Embedding failed [Restaurant ID: $id]: " . $response->body());
            throw new \Exception("Embeddings API error: " . $response->status());
        }

        $values = $response->json('predictions.0.embedding.values');

        if (!is_array($values)) {
            Log::error("Embedding parse error [Restaurant ID: $restaurantId]: malformed response", [
                'response_json' => $response->json()
            ]);
            throw new \Exception("Embeddings API returned invalid data");
        }

        return $values;
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            throw new \Exception("Vector dimensions must match");
        }

        $dot = 0.0;
        $magA = 0.0;
        $magB = 0.0;
        foreach ($a as $i => $val) {
            $dot  += $val * $b[$i];
            $magA += $val * $val;
            $magB += $b[$i] * $b[$i];
        }

        if ($magA === 0.0 || $magB === 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($magA) * sqrt($magB));
    }

    /**
     * Genera un vector de embedding falso (solo para desarrollo o seeding).
     *
     * @return array
     */
    private function generateDummyEmbedding(): array
    {
        return array_map(fn () => round(mt_rand() / mt_getrandmax(), 4), range(1, 256)); // 256 dimensiones
    }
}
