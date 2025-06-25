<?php

namespace App\Services;

use App\Models\ChatConversation;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    public function __construct(private RestaurantContextService $contextService) {}

    public function processMessage(string $message, string $sessionId): array
    {
        $conv = $this->getOrCreateConversation($sessionId);
        $this->saveMessage($conv, 'user', $message);

        $ctx   = $this->contextService->getRelevantContext($message);
        $prompt= $this->buildContextualPrompt($message, $ctx, $conv);
        $reply = $this->generateResponse($prompt);

        $this->saveMessage($conv, 'assistant', $reply, $ctx);

        return [
            'response'       => $reply,
            'context_used'   => !empty($ctx),
            'conversation_id'=> $conv->id,
            'context'        => $ctx,
        ];
    }

    private function buildContextualPrompt(string $msg, array $ctx, ChatConversation $conv): string
    {
        $p  = "Eres el asistente de MiCiudadGourmet. Resumes de forma profesional y concisa.\n\n";

        if (!empty($ctx['restaurants'])) {
            $p .= "ENCONTRADO EN LA PLATAFORMA:\n";
            foreach ($ctx['restaurants'] as $r) {
                $cats = !empty($r['categories'])
                    ? implode(', ', $r['categories'])
                    : '—';
                $p .= "- {$r['name']} ({$cats}): {$r['address']}. {$r['description']}\n";
            }
            $p .= "\nINDICA SÓLO ESTAS OPCIONES, SIN PEDIR MÁS DATOS.\n\n";
        }

        $hist = $conv->messages()->latest()->limit(10)->get()->reverse();
        if ($hist->isNotEmpty()) {
            $p .= "HISTORIAL:\n";
            foreach ($hist as $m) {
                $actor = $m->role==='user'? 'Usuario':'Asistente';
                $p .= "{$actor}: {$m->content}\n";
            }
            $p .= "\n";
        }

        $p .= "PREGUNTA: {$msg}\n";
        $p .= "RESPONDE de forma directa y profesional.";
        return $p;
    }

    private function generateResponse(string $prompt): string
    {
        try {
            return Gemini::generativeModel('gemini-1.5-flash')
                         ->generateContent($prompt)
                         ->text();
        } catch (\Throwable $e) {
            Log::error("Gemini error: ".$e->getMessage());
            return "Lo siento, algo falló. Intenta de nuevo.";
        }
    }

    private function getOrCreateConversation(string $sid): ChatConversation
    {
        return ChatConversation::firstOrCreate(
            ['session_id'=>$sid],
            ['user_id'=>Auth::id(), 'title'=>'Chat '.now()->format('d/m/Y H:i')]
        );
    }

    private function saveMessage($conv, string $role, string $text, array $ctx=null)
    {
        $conv->messages()->create([
            'role'         => $role,
            'content'      => $text,
            'context_data' => $ctx
        ]);
    }
}
