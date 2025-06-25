@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div id="chat" class="border rounded p-3" style="height:400px; overflow-y:auto;"></div>
  <div class="input-group mt-2">
    <input id="msg" class="form-control" placeholder="Escribe tu mensaje…" />
    <button id="btn" class="btn btn-primary">Enviar</button>
  </div>
</div>

@push('scripts')
<script>
const chat  = document.getElementById('chat'),
      input = document.getElementById('msg'),
      btn   = document.getElementById('btn');

// Añade un mensaje de texto
function add(role, text) {
  const div = document.createElement('div');
  div.className = role==='user'? 'text-end mb-2':'text-start mb-2';
  div.innerHTML = `<span class="badge ${role==='user'?'bg-primary':'bg-secondary'}">${text}</span>`;
  chat.appendChild(div);
  chat.scrollTop = chat.scrollHeight;
}

// Añade una tarjeta de restaurante con enlace
function addCard(r) {
  const card = document.createElement('div');
  card.className = 'card mb-2';
  card.style = 'max-width:300px';
  card.innerHTML = `
    <img src="${r.image||''}" class="card-img-top" alt="${r.name}" onerror="this.remove()">
    <div class="card-body">
      <h5 class="card-title">${r.name}</h5>
      <p class="card-text">${r.description}</p>
      <p class="card-text"><small class="text-muted">${r.address}</small></p>
      <a href="/restaurants/${r.id}" target="_blank" class="btn btn-sm btn-outline-primary">Ver detalle</a>
    </div>`;
  chat.appendChild(card);
  chat.scrollTop = chat.scrollHeight;
}

// Envía el mensaje al backend
async function send() {
  const txt = input.value.trim();
  if (!txt) return;
  add('user', txt);
  input.value = '';
  add('assistant','…');
  try {
    const sid = localStorage.getItem('sid') || Date.now().toString();
    localStorage.setItem('sid', sid);
    const res = await fetch('/api/chatbot/send', {
      method:'POST',
      headers:{
        'Content-Type':'application/json',
        'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ message: txt, session_id: sid })
    });
    const json = await res.json();
    chat.lastChild.remove(); // quita “…”
    if (json.success) {
      add('assistant', json.data.response);
      if (json.data.context_used && json.data.context.restaurants) {
        json.data.context.restaurants.forEach(r => addCard(r));
      }
    } else {
      add('assistant','Error en la respuesta');
    }
  } catch {
    chat.lastChild.remove();
    add('assistant','Error de red');
  }
}

// Al cargar, saludo inicial
document.addEventListener('DOMContentLoaded', () => {
  add('assistant','¡Hola! Soy tu asistente de MiCiudadGourmet. ¿En qué puedo ayudarte hoy?');
  input.focus();
});

btn.onclick = send;
input.addEventListener('keyup', e => e.key==='Enter' && send());
</script>
@endpush
@endsection
