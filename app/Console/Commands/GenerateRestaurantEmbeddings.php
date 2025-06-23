<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmbeddingService;

class GenerateRestaurantEmbeddings extends Command
{
    protected $signature = 'embeddings:generate {--restaurant_id=}';
    protected $description = 'Genera embeddings para restaurantes';

    public function handle(EmbeddingService $embeddingService)
    {
        $this->info('Iniciando generación de embeddings...');

        if ($restaurantId = $this->option('restaurant_id')) {
            // Generar para un restaurante específico
            $restaurant = \App\Models\Restaurant::with(['categories', 'reviews'])->find($restaurantId);
            
            if (!$restaurant) {
                $this->error("Restaurante con ID {$restaurantId} no encontrado.");
                return 1;
            }

            try {
                $embeddingService->createRestaurantEmbedding($restaurant);
                $this->info("Embedding generado para: {$restaurant->name}");
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
                return 1;
            }
        } else {
            // Generar para todos los restaurantes
            $results = $embeddingService->generateAllRestaurantEmbeddings();
            
            $this->info("Embeddings generados:");
            $this->info("- Exitosos: {$results['success']}");
            $this->info("- Fallidos: {$results['failed']}");
            
            if (!empty($results['errors'])) {
                $this->error("Errores:");
                foreach ($results['errors'] as $error) {
                    $this->error("- {$error}");
                }
            }
        }

        return 0;
    }
}
