<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\RestaurantEmbedding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EmbeddingService
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent';
    }

    /**
     * Genera embedding para un restaurante específico
     */
    public function createRestaurantEmbedding(Restaurant $restaurant): array
    {
        // Construir texto descriptivo del restaurante
        $text = $this->buildRestaurantText($restaurant);
        
        // Generar embedding usando Google Gemini
        $embedding = $this->generateEmbedding($text);
        
        if (!$embedding) {
            throw new \Exception("No se pudo generar embedding para restaurante {$restaurant->id}");
        }

        // Guardar en base de datos
        RestaurantEmbedding::updateOrCreate(
            ['restaurant_id' => $restaurant->id],
            [
                'embedding' => $embedding,
                'text_content' => $text
            ]
        );

        return $embedding;
    }

    /**
     * Construye el texto descriptivo del restaurante
     */
    private function buildRestaurantText(Restaurant $restaurant): string
    {
        $textParts = [
            $restaurant->name,
            $restaurant->description ?? '',
            $restaurant->address,
            $restaurant->phone ?? '',
        ];

        // Agregar categorías
        if ($restaurant->categories->isNotEmpty()) {
            $categories = $restaurant->categories->pluck('name')->join(' ');
            $textParts[] = "Categorías: " . $categories;
        }

        // Agregar información de reseñas si existe
        if ($restaurant->reviews->isNotEmpty()) {
            $avgRating = $restaurant->reviews->avg('rating');
            $textParts[] = "Calificación promedio: " . round($avgRating, 1) . " estrellas";
            
            // Agregar algunos comentarios representativos
            $topComments = $restaurant->reviews()
                ->whereNotNull('comment')
                ->orderBy('rating', 'desc')
                ->limit(3)
                ->pluck('comment')
                ->join(' ');
            
            if ($topComments) {
                $textParts[] = "Comentarios: " . $topComments;
            }
        }

        return implode(' ', array_filter($textParts));
    }

    /**
     * Genera embedding usando Google Gemini API
     */
    public function generateEmbedding(string $text): ?array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'model' => 'models/text-embedding-004',
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['embedding']['values'] ?? null;
            }

            Log::error('Error generando embedding: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Excepción generando embedding: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca restaurantes similares usando similitud coseno
     */
    public function findSimilarRestaurants(string $query, int $limit = 5): array
    {
        // Generar embedding para la consulta
        $queryEmbedding = $this->generateEmbedding($query);
        
        if (!$queryEmbedding) {
            return [];
        }

        // Obtener todos los embeddings de restaurantes
        $restaurantEmbeddings = RestaurantEmbedding::with('restaurant.categories', 'restaurant.reviews')
            ->get();

        $similarities = [];

        foreach ($restaurantEmbeddings as $embeddingRecord) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $embeddingRecord->embedding);
            
            $similarities[] = [
                'restaurant' => $embeddingRecord->restaurant,
                'similarity' => $similarity,
                'text_content' => $embeddingRecord->text_content
            ];
        }

        // Ordenar por similitud descendente
        usort($similarities, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($similarities, 0, $limit);
    }

    /**
     * Calcula similitud coseno entre dos vectores
     */
    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $normA += $vectorA[$i] * $vectorA[$i];
            $normB += $vectorB[$i] * $vectorB[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dotProduct / ($normA * $normB);
    }

    /**
     * Genera embeddings para todos los restaurantes
     */
    public function generateAllRestaurantEmbeddings(): array
    {
        $restaurants = Restaurant::with(['categories', 'reviews'])->get();
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($restaurants as $restaurant) {
            try {
                $this->createRestaurantEmbedding($restaurant);
                $results['success']++;
                
                // Pausa pequeña para no sobrecargar la API
                usleep(200000); // 200ms
                
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Restaurante {$restaurant->id}: " . $e->getMessage();
                Log::error("Error generando embedding para restaurante {$restaurant->id}: " . $e->getMessage());
            }
        }

        return $results;
    }
}
