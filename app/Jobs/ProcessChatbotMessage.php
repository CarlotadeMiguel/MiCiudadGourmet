<?php

namespace App\Jobs;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatbotService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessChatbotMessage implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $conversationId;
    protected $userMessage;
    protected $sessionId;

    /**
     * Create a new job instance.
     */
    public function __construct($conversationId, $userMessage, $sessionId = null)
    {
        $this->conversationId = $conversationId;
        $this->userMessage = $userMessage;
        $this->sessionId = $sessionId;
    }

    /**
     * Execute the job.
     */
    public function handle(ChatbotService $chatbotService): void
    {
        try {
            // Procesar mensaje con contexto de restaurantes
            $response = $chatbotService->processMessage(
                $this->userMessage, 
                $this->conversationId,
                $this->sessionId
            );

            // Guardar respuesta del bot
            ChatMessage::create([
                'conversation_id' => $this->conversationId,
                'role' => 'assistant',
                'content' => $response['message'],
                'context_data' => json_encode($response['context'] ?? [])
            ]);

            Log::info('Chatbot message processed successfully', [
                'conversation_id' => $this->conversationId,
                'user_message' => $this->userMessage
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing chatbot message: ' . $e->getMessage(), [
                'conversation_id' => $this->conversationId,
                'user_message' => $this->userMessage,
                'error' => $e->getTraceAsString()
            ]);
            
            // Crear mensaje de error para el usuario
            ChatMessage::create([
                'conversation_id' => $this->conversationId,
                'role' => 'assistant',
                'content' => 'Lo siento, ha ocurrido un error. Por favor, intenta de nuevo.',
                'context_data' => json_encode(['error' => true])
            ]);
        }
    }
}
