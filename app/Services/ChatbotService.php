<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private RestaurantContextService $contextService;

    public function __construct(RestaurantContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * Procesa el mensaje de un usuario: guarda, obtiene contexto, invoca LLM y almacena respuesta.
     */
    public function processMessage(string $message, string $sessionId): array
    {
        // 1. Crear o recuperar conversación
        $conversation = $this->getOrCreateConversation($sessionId);

        // 2. Guardar mensaje del usuario
        $this->saveMessage($conversation, 'user', $message);

        // 3. Obtener contexto relevante de la plataforma
        $context = $this->contextService->getRelevantContext($message);

        // 4. Construir prompt enriquecido con contexto y historial
        $prompt = $this->buildContextualPrompt($message, $context, $conversation);

        // 5. Generar respuesta usando Gemini LLM
        $responseText = $this->generateResponse($prompt);

        // 6. Guardar respuesta del asistente junto al contexto usado
        $this->saveMessage($conversation, 'assistant', $responseText, $context);

        return [
            'response'      => $responseText,
            'context_used'  => !empty($context),
            'conversation_id' => $conversation->id,
            'context'       => $context,
        ];
    }

    /**
     * Construye el prompt incluyendo información de restaurantes, categorías, populares y el historial.
     */
    private function buildContextualPrompt(string $userMessage, array $context, ChatConversation $conversation): string
    {
        $prompt = "Eres un asistente experto en restaurantes de MiCiudadGourmet. ";
        $prompt .= "Ayuda al usuario a encontrar lugares, recomendar y resolver dudas.\n\n";

        if (!empty($context)) {
            $prompt .= "INFORMACIÓN DE LA PLATAFORMA:\n";
            if (isset($context['restaurants'])) {
                $prompt .= "• Restaurantes recomendados:\n";
                foreach ($context['restaurants'] as $r) {
                    $prompt .= "  - {$r['name']} en {$r['address']}\n";
                }
            }
            if (isset($context['categories'])) {
                $prompt .= "• Categorías disponibles:\n";
                foreach ($context['categories'] as $c) {
                    $prompt .= "  - {$c['name']} ({$c['restaurants_count']} locales)\n";
                }
            }
            if (isset($context['popular'])) {
                $prompt .= "• Más populares:\n";
                foreach ($context['popular'] as $p) {
                    $rating = $p['average_rating'] ?? 'N/A';
                    $prompt .= "  - {$p['name']} ({$rating}/5)\n";
                }
            }
            $prompt .= "\n";
        }

        // Historial de los últimos 10 mensajes
        $messages = $conversation->messages()->latest()->limit(10)->get()->reverse();
        if ($messages->isNotEmpty()) {
            $prompt .= "HISTORIAL:\n";
            foreach ($messages as $msg) {
                $actor = $msg->role === 'user' ? 'Usuario' : 'Asistente';
                $prompt .= "{$actor}: {$msg->content}\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "PREGUNTA ACTUAL: {$userMessage}\n";
        $prompt .= "RESPONDE de forma amigable, precisa y usando los datos de MiCiudadGourmet.";

        return $prompt;
    }

    /**
     * Llama a Gemini LLM para generar la respuesta.
     */
    private function generateResponse(string $prompt): string
    {
        try {
            $result = Gemini::generativeModel('gemini-1.5-flash')
                ->generateContent($prompt);
            return $result->text();
        } catch (\Exception $e) {
            Log::error("Error Gemini: {$e->getMessage()}");
            return "Lo siento, no pude procesar tu solicitud en este momento.";
        }
    }

    /**
     * Recupera o crea la conversación en la base de datos.
     */
    private function getOrCreateConversation(string $sessionId): ChatConversation
    {
        return ChatConversation::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => Auth::id(),
                'title'   => 'Chat ' . now()->format('d/m/Y H:i'),
            ]
        );
    }

    /**
     * Guarda un mensaje en la conversación, opcionalmente con contexto.
     */
    private function saveMessage(
        ChatConversation $conversation,
        string $role,
        string $content,
        array $contextData = null
    ): ChatMessage {
        return $conversation->messages()->create([
            'role'         => $role,
            'content'      => $content,
            'context_data' => $contextData,
        ]);
    }
}
