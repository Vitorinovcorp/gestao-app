@extends('layouts.app')

@section('title', 'Chat Inteligente Inovcorp')
@section('header', 'Chat Inteligente Inovcorp')

@section('content')
<style>
    .chat-full-width {
        margin: -1.5rem -1.5rem -1.5rem -1.5rem;
        height: calc(100vh - 120px);
        display: flex;
        flex-direction: column;
    }
    
    @media (max-width: 768px) {
        .chat-full-width {
            margin: -1rem -1rem -1rem -1rem;
            height: calc(100vh - 100px);
        }
    }
    
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
    }
    
    .message-user {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
    }
    
    .message-user .bubble {
        background-color: #4f46e5;
        color: white;
        border-radius: 1rem 1rem 0.25rem 1rem;
        padding: 0.75rem 1rem;
        max-width: 70%;
    }
    
    .message-bot {
        display: flex;
        justify-content: flex-start;
        margin-bottom: 1rem;
    }
    
    .message-bot .bubble {
        background-color: #f3f4f6;
        color: #1f2937;
        border-radius: 1rem 1rem 1rem 0.25rem;
        padding: 0.75rem 1rem;
        max-width: 70%;
    }
    
    .typing-indicator {
        display: flex;
        justify-content: flex-start;
        margin-bottom: 1rem;
    }
    
    .typing-indicator .bubble {
        background-color: #f3f4f6;
        padding: 0.75rem 1rem;
        border-radius: 1rem 1rem 1rem 0.25rem;
    }
    
    .typing-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #9ca3af;
        margin: 0 2px;
        animation: typing 1.4s infinite ease-in-out;
    }
    
    .typing-dot:nth-child(1) { animation-delay: 0s; }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    
    @keyframes typing {
        0%, 60%, 100% {
            transform: translateY(0);
            opacity: 0.4;
        }
        30% {
            transform: translateY(-10px);
            opacity: 1;
        }
    }
    
    .quick-buttons {
        padding: 1rem 1.5rem;
        background-color: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .chat-input-area {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background-color: white;
    }
</style>

<div class="chat-full-width">
    <div class="quick-buttons">
        <p class="text-sm text-gray-600 mb-2">Sugestões rápidas:</p>
        <div class="flex flex-wrap gap-2">
            <button onclick="sendQuickQuestion('Volume em Negociação')" 
                    class="px-3 py-1.5 bg-indigo-100 text-indigo-700 text-sm rounded-full hover:bg-indigo-200 transition cursor-pointer">
                Volume em Negociação
            </button>
            <button onclick="sendQuickQuestion('Clientes mais ativos')" 
                    class="px-3 py-1.5 bg-green-100 text-green-700 text-sm rounded-full hover:bg-green-200 transition cursor-pointer">
                Clientes mais ativos
            </button>
            <button onclick="sendQuickQuestion('Negócios em Follow Up')" 
                    class="px-3 py-1.5 bg-yellow-100 text-yellow-700 text-sm rounded-full hover:bg-yellow-200 transition cursor-pointer">
                Negócios em Follow Up
            </button>
        </div>
    </div>
    
    <div id="chat-messages" class="chat-messages">
        <div class="message-bot">
            <div class="bubble">
                <p class="text-sm">Olá! Sou seu assistente de CRM.</p>
            </div>
        </div>
    </div>
    
    <div class="chat-input-area">
        <div class="flex gap-2">
            <input type="text" 
                   id="chat-input" 
                   placeholder="Digite sua pergunta..." 
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   onkeypress="if(event.key === 'Enter') sendMessage()">
            <button onclick="sendMessage()" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Enviar
            </button>
        </div>
    </div>
</div>

<script>
    const messagesContainer = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    
    function sendQuickQuestion(question) {
        chatInput.value = question;
        sendMessage();
    }
    
    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;
        
        addMessage(message, 'user');
        chatInput.value = '';
        
        showTypingIndicator();
        
        fetch('/api/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            hideTypingIndicator();
            
            if (data.error) {
                addMessage('❌ ' + data.error, 'bot');
            } else {
                addMessage(data.answer, 'bot');
                
                if (data.action) {
                    handleAction(data.action);
                }
            }
        })
        .catch(error => {
            hideTypingIndicator();
            addMessage('❌ Erro ao processar sua pergunta. Tente novamente.', 'bot');
        });
    }
    
    function addMessage(text, sender) {
        const div = document.createElement('div');
        div.className = sender === 'user' ? 'message-user' : 'message-bot';
        
        let formattedText = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        formattedText = formattedText.replace(/\n/g, '<br>');
        
        div.innerHTML = `<div class="bubble"><div class="text-sm">${formattedText}</div></div>`;
        
        messagesContainer.appendChild(div);
        scrollToBottom();
    }
    
    function showTypingIndicator() {
        const div = document.createElement('div');
        div.id = 'typing-indicator';
        div.className = 'typing-indicator';
        div.innerHTML = `
            <div class="bubble">
                <div class="flex space-x-1">
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                </div>
            </div>
        `;
        messagesContainer.appendChild(div);
        scrollToBottom();
    }
    
    function hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) indicator.remove();
    }
    
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    function handleAction(action) {
        if (action.type === 'open_deal' && action.id) {
            if (confirm(`Deseja abrir o negócio ID ${action.id}?`)) {
                window.location.href = `/deals/${action.id}`;
            }
        } else if (action.type === 'open_entity' && action.id) {
            if (confirm(`Deseja abrir o cliente ID ${action.id}?`)) {
                window.location.href = `/entities/${action.id}`;
            }
        }
    }
</script>
@endsection