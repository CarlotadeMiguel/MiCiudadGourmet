// app/Services/ChatbotService.php
<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Auth;

class ChatbotService
{
    private RestaurantContextService $contextService;
    
    public function __construct(RestaurantContextService $contextService)
    {
        $this->contextService = $contextService;
    }
    
    public function processMessage(string $message, string $sessionId): array
    {
        // Obtener o crear conversación
        $conversation = $this->getOrCreateConversation($sessionId);
        
        // Guardar mensaje del usuario
        $userMessage = $this->saveMessage($conversation, 'user', $message);
        
        // Obtener contexto relevante
        $context = $this->contextService->getRelevantContext($message);
        
        // Construir prompt con contexto
        $prompt = $this->buildContextualPrompt($message, $context, $conversation);
        
        // Generar respuesta con Gemini
        $response = $this->generateResponse($prompt);
        
        // Guardar respuesta del asistente
        $assistantMessage = $this->saveMessage($conversation, 'assistant', $response, $context);
        
        return [
            'response' => $response,
            'context_used' => !empty($context),
            'conversation_id' => $conversation->id
        ];
    }
    
    private function buildContextualPrompt(string $userMessage, array $context, ChatConversation $conversation): string
    {
        $prompt = "Eres un asistente experto en restaurantes para la plataforma MiCiudadGourmet. ";
        $prompt .= "Ayudas a los usuarios a encontrar restaurantes, obtener recomendaciones y resolver dudas sobre gastronomía.\n\n";
        
        // Agregar contexto de la aplicación si existe
        if (!empty($context)) {
            $prompt .= "INFORMACIÓN RELEVANTE DE LA PLATAFORMA:\n";
            
            if (isset($context['restaurants'])) {
                $prompt .= "Restaurantes disponibles:\n";
                foreach ($context['restaurants'] as $restaurant) {
                    $prompt .= "- {$restaurant['name']} en {$restaurant['address']}\n";
                }
            }
            
            if (isset($context['categories'])) {
                $prompt .= "Categorías disponibles:\n";
                foreach ($context['categories'] as $category) {
                    $prompt .= "- {$category['name']} ({$category['restaurants_count']} restaurantes)\n";
                }
            }
            
            if (isset($context['popular'])) {
                $prompt .= "Restaurantes populares:\n";
                foreach ($context['popular'] as $restaurant) {
                    $rating = $restaurant['reviews_avg_rating'] ?? 'Sin calificación';
                    $prompt .= "- {$restaurant['name']} (Calificación: {$rating})\n";
                }
            }
            
            $prompt .= "\n";
        }
        
        // Agregar historial de conversación
        $recentMessages = $conversation->messages()
            ->latest()
            ->limit(10)
            ->get()
            ->reverse();
            
        if ($recentMessages->count() > 0) {
            $prompt .= "HISTORIAL DE CONVERSACIÓN:\n";
            foreach ($recentMessages as $msg) {
                $role = $msg->role === 'user' ? 'Usuario' : 'Asistente';
                $prompt .= "{$role}: {$msg->content}\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "PREGUNTA ACTUAL: {$userMessage}\n\n";
        $prompt .= "Responde de manera útil, amigable y específica basándote en la información de MiCiudadGourmet.";
        
        return $prompt;
    }
    
    private function generateResponse(string $prompt): string
    {
        try {
            $result = Gemini::generativeModel('gemini-1.5-flash')
                ->generateContent($prompt);
                
            return $result->text();
        } catch (\Exception $e) {
            \Log::error('Error generating chatbot response: ' . $e->getMessage());
            return 'Lo siento, no pude procesar tu consulta en este momento.';
        }
    }
    
    private function getOrCreateConversation(string $sessionId): ChatConversation
    {
        return ChatConversation::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => Auth::id(),
                'title' => 'Conversación ' . now()->format('d/m/Y H:i')
            ]
        );
    }
    
    private function saveMessage(ChatConversation $conversation, string $role, string $content, array $contextData = null): ChatMessage
    {
        return $conversation->messages()->create([
            'role' => $role,
            'content' => $content,
            'context_data' => $contextData
        ]);
    }
}
