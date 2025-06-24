@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Asistente Virtual - MiCiudadGourmet</h4>
                </div>
                <div class="card-body">
                    <div id="chat-messages" class="chat-container mb-3">
                        <!-- Los mensajes se cargarán aquí -->
                    </div>
                    <div class="input-group">
                        <input type="text" id="message-input" class="form-control" 
                               placeholder="Pregúntame sobre restaurantes...">
                        <div class="input-group-append">
                            <button class="btn btn-primary" id="send-button">
                                Enviar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-container {
    height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 15px;
    background-color: #f8f9fa;
}

.message {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 10px;
}

.user-message {
    background-color: #007bff;
    color: white;
    margin-left: 20%;
    text-align: right;
}

.assistant-message {
    background-color: white;
    margin-right: 20%;
    border: 1px solid #ddd;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    
    // Generar ID de sesión único
    const sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    // Cargar historial si existe
    loadConversationHistory();
    
    // Event listeners
    sendButton.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    async function sendMessage() {
        const message = messageInput.value.trim();
        if (!message) return;
        
        // Mostrar mensaje del usuario
        addMessage(message, 'user');
        messageInput.value = '';
        sendButton.disabled = true;
        
        // Mostrar indicador de escritura
        showTypingIndicator();
        
        try {
            const response = await fetch('/api/chatbot/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: message,
                    session_id: sessionId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                addMessage(data.data.response, 'assistant');
            } else {
                addMessage('Lo siento, ocurrió un error.', 'assistant');
            }
        } catch (error) {
            console.error('Error:', error);
            addMessage('Error de conexión.', 'assistant');
        } finally {
            hideTypingIndicator();
            sendButton.disabled = false;
            messageInput.focus();
        }
    }
    
    function addMessage(content, role) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${role}-message`;
        messageDiv.textContent = content;
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    function showTypingIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'typing-indicator';
        indicator.className = 'message assistant-message';
        indicator.innerHTML = '<em>Escribiendo...</em>';
        messagesContainer.appendChild(indicator);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    function hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
    
    async function loadConversationHistory() {
        try {
            const response = await fetch(`/api/chatbot/history?session_id=${sessionId}`);
            const data = await response.json();
            
            if (data.success && data.data.length > 0) {
                data.data.forEach(message => {
                    addMessage(message.content, message.role);
                });
            }
        } catch (error) {
            console.error('Error loading history:', error);
        }
    }
});
</script>
@endsection
