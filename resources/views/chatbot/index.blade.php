@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-robot"></i> Mi Ciudad Gourmet - Chatbot</h4>
                </div>
                <div class="card-body">
                    <!-- Contenedor del chat -->
                    <div id="chat-container" class="chat-container mb-3">
                        <div id="chat-messages" class="chat-messages">
                            <!-- Los mensajes aparecerán aquí -->
                            <div class="message assistant-message">
                                <div class="message-content">
                                    <strong>Asistente:</strong> ¡Hola! Soy tu asistente de Mi Ciudad Gourmet. ¿En qué puedo ayudarte hoy?
                                </div>
                                <div class="message-timestamp">{{ now()->format('H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedor para tarjetas de restaurantes -->
                    <div id="restaurant-cards" class="restaurant-cards mb-3">
                        <!-- Las tarjetas de restaurantes aparecerán aquí -->
                    </div>

                    <!-- Formulario de entrada -->
                    <div class="input-group">
                        <input type="text" id="user-input" class="form-control" 
                               placeholder="Escribe tu mensaje aquí..." 
                               autocomplete="off">
                        <div class="input-group-append">
                            <button id="send-button" class="btn btn-primary" type="button">
                                <i class="fas fa-paper-plane"></i> Enviar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para el chatbot */
.chat-container {
    height: 400px;
    border: 1px solid #ddd;
    border-radius: 0.375rem;
    overflow: hidden;
}

.chat-messages {
    height: 100%;
    overflow-y: auto;
    padding: 15px;
    background-color: #f8f9fa;
}

.message {
    margin-bottom: 15px;
    animation: fadeIn 0.3s ease-in;
}

.message-content {
    padding: 10px 15px;
    border-radius: 18px;
    max-width: 80%;
    word-wrap: break-word;
}

.user-message .message-content {
    background-color: #007bff;
    color: white;
    margin-left: auto;
    text-align: right;
}

.assistant-message .message-content {
    background-color: white;
    color: #333;
    border: 1px solid #ddd;
}

.message-timestamp {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 5px;
    text-align: center;
}

.restaurant-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.restaurant-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    width: 300px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.restaurant-card:hover {
    transform: translateY(-2px);
}

.restaurant-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.restaurant-card-body {
    padding: 15px;
}

.restaurant-card h5 {
    margin: 0 0 8px 0;
    color: #333;
}

.restaurant-card p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Indicador de escritura */
.typing-indicator {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 10px 15px;
}

.typing-dots {
    display: flex;
    gap: 3px;
}

.typing-dots span {
    width: 8px;
    height: 8px;
    background-color: #007bff;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.typing-dots span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userInput = document.getElementById('user-input');
    const sendButton = document.getElementById('send-button');
    const chatMessages = document.getElementById('chat-messages');
    const restaurantCards = document.getElementById('restaurant-cards');

    // Función para agregar mensajes al chat
    function addMessage(message, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;
        
        const now = new Date();
        const timeString = now.toLocaleTimeString('es-ES', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        messageDiv.innerHTML = `
            <div class="message-content">
                <strong>${sender === 'user' ? 'Tú' : 'Asistente'}:</strong> ${message}
            </div>
            <div class="message-timestamp">${timeString}</div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Función para mostrar indicador de escritura
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.className = 'message assistant-message';
        typingDiv.innerHTML = `
            <div class="message-content typing-indicator">
                <span>El asistente está escribiendo</span>
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;
        
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Función para ocultar indicador de escritura
    function hideTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    // Función para mostrar tarjeta de restaurante
    function displayRestaurantCard(restaurant) {
        const cardDiv = document.createElement('div');
        cardDiv.className = 'restaurant-card';
        
        cardDiv.innerHTML = `
            ${restaurant.image ? `<img src="${restaurant.image}" alt="${restaurant.name}">` : ''}
            <div class="restaurant-card-body">
                <h5>${restaurant.name}</h5>
                <p><strong>Dirección:</strong> ${restaurant.address || 'No disponible'}</p>
                <p><strong>Descripción:</strong> ${restaurant.description || 'No disponible'}</p>
            </div>
        `;
        
        restaurantCards.appendChild(cardDiv);
    }

// Función principal para enviar mensaje
async function sendMessage() {
    const message = userInput.value.trim();
    if (!message) return;

    // Mostrar mensaje del usuario
    addMessage(message, 'user');
    userInput.value = '';
    
    // Mostrar indicador de escritura
    showTypingIndicator();
    
    // Generar o recuperar un session_id
    let sessionId = localStorage.getItem('chatbot_session_id');
    if (!sessionId) {
        sessionId = 'session_' + Date.now(); // Genera un ID único basado en timestamp
        localStorage.setItem('chatbot_session_id', sessionId);
    }
    
    try {
        const response = await fetch('/api/chatbot/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                message: message,
                session_id: sessionId // Añadir el session_id requerido
            })
        });

        // Verificar si la respuesta es JSON antes de intentar analizarla
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('La respuesta no es JSON válido');
        }

        const data = await response.json();

        // Ocultar indicador de escritura
        hideTypingIndicator();

        if (data.success) {
            // Mostrar respuesta del bot
            addMessage(data.data.response, 'assistant');

            // Limpiar tarjetas anteriores si hay nuevas
            if (data.data.context_used && data.data.context.restaurants) {
                restaurantCards.innerHTML = '';
                
                // Mostrar tarjetas de restaurantes
                data.data.context.restaurants.forEach(restaurant => {
                    displayRestaurantCard({
                        name: restaurant.name,
                        address: restaurant.address,
                        description: restaurant.description,
                        image: restaurant.image
                    });
                });
            }
        } else {
            addMessage('Ha ocurrido un error en la respuesta del servidor.', 'assistant');
        }
    } catch (error) {
        hideTypingIndicator();
        addMessage('Error de conexión con el servidor: ' + error.message, 'assistant');
        console.error('Error:', error);
    }
}
    // Event listeners
    sendButton.addEventListener('click', sendMessage);
    
    userInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendMessage();
        }
    });

    // Enfocar el input al cargar la página
    userInput.focus();
});
</script>
@endsection
