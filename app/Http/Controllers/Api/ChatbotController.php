// app/Http/Controllers/Api/ChatbotController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    private ChatbotService $chatbotService;
    
    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }
    
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'required|string'
        ]);
        
        try {
            $result = $this->chatbotService->processMessage(
                $request->message,
                $request->session_id
            );
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Chatbot error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error procesando el mensaje'
            ], 500);
        }
    }
    
    public function getConversationHistory(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);
        
        $conversation = ChatConversation::where('session_id', $request->session_id)
            ->with('messages')
            ->first();
            
        if (!$conversation) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => $conversation->messages
        ]);
    }
}
