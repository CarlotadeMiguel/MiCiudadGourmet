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
            $res = Http::post("{$this->apiUrl}?key={$this->apiKey}", [
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
    public function findSimilarRestaurants(string $query, int $limit = 5): array
    {
        $queryEmbedding = $this->generateEmbedding($query);
        
        $restaurantEmbeddings = RestaurantEmbedding::with('restaurant')->get();
        $similarities = [];

        foreach ($restaurantEmbeddings as $embedding) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $embedding->embedding);
            $similarities[] = [
                'restaurant' => $embedding->restaurant,
                'similarity' => $similarity
            ];
        }

        // Ordenar por similitud descendente
        usort($similarities, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        // Tomar los más similares
        $topSimilar = array_slice($similarities, 0, $limit);

        return array_map(function ($item) {
            $restaurant = $item['restaurant'];
            return [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'description' => $restaurant->description,
                'address' => $restaurant->address,
                'phone' => $restaurant->phone,
                'email' => $restaurant->email,
                'image' => $restaurant->image,
                'similarity_score' => round($item['similarity'], 3)
            ];
        }, $topSimilar);
    }

    /**
     * Construye el texto descriptivo del restaurante para embeddings.
     */
    private function buildText(Restaurant $restaurant): string
    {
        $text = $restaurant->name . ' ';
        $text .= $restaurant->description . ' ';
        $text .= 'Ubicado en ' . $restaurant->address . ' ';
        
        if ($restaurant->category) {
            $text .= 'Categoría: ' . $restaurant->category->name . ' ';
        }
        
        return trim($text);
    }

    /**
     * Genera embedding para una consulta de texto.
     */
    private function generateEmbedding(string $text): array
    {
        $response = Http::post("{$this->apiUrl}?key={$this->apiKey}", [
            'model' => 'models/text-embedding-004',
            'content' => ['parts' => [['text' => $text]]]
        ]);

        if (!$response->successful()) {
            Log::error("Error generating embedding: " . $response->body());
            throw new \Exception("Failed to generate embedding");
        }

        return $response->json()['embedding']['values'];
    }

    /**
     * Calcula la similitud coseno entre dos vectores.
     */
    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB)) {
            throw new \Exception("Vector dimensions must match");
        }

        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $magnitudeA += $vectorA[$i] * $vectorA[$i];
            $magnitudeB += $vectorB[$i] * $vectorB[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }
}
