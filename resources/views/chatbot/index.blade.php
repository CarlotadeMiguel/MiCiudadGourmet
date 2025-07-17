@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div id="chat" class="border rounded p-3" style="height:400px; overflow-y:auto;"></div>
  <div class="input-group mt-2">
    <input id="msg" class="form-control" placeholder="Escribe tu mensaje…" autocomplete="off"/>
    <button id="btn" class="btn btn-primary">Enviar</button>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Referencias al DOM
const chat  = document.getElementById('chat'),
      input = document.getElementById('msg'),
      btn   = document.getElementById('btn');

// Escapa HTML para evitar XSS
function escapeHtml(str) {
  return str.replace(/[&<>'"]/g, m =>
    ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[m])
  );
}

// Añade un mensaje (usuario o asistente) al chat
function add(role, text) {
  const div = document.createElement('div');
  div.className = role==='user' ? 'text-end mb-2' : 'text-start mb-2';
  div.innerHTML = `<span class="badge ${role==='user'?'bg-primary':'bg-secondary'}">
                     ${escapeHtml(text)}
                   </span>`;
  chat.appendChild(div);
  chat.scrollTop = chat.scrollHeight;
}

// Añade una tarjeta de restaurante con enlace al detalle
function addCard(r) {
  const categories = Array.isArray(r.categories) ? r.categories.join(', ') : '';
  const imgTag = r.image
    ? `<img src="${r.image}" class="card-img-top" alt="${escapeHtml(r.name)}" onerror="this.remove()">`
    : '';
  const cardHtml = `
    <div class="card mb-2" style="max-width:300px">
      ${imgTag}
      <div class="card-body">
        <h5 class="card-title">${escapeHtml(r.name)}</h5>
        ${categories ? `<p><em>${escapeHtml(categories)}</em></p>` : ''}
        <p class="card-text">${escapeHtml(r.description)}</p>
        <p class="card-text"><small class="text-muted">${escapeHtml(r.address)}</small></p>
        <a href="/restaurants/${r.id}" target="_blank" class="btn btn-sm btn-outline-primary">
          Ver detalle
        </a>
      </div>
    </div>`;
  chat.insertAdjacentHTML('beforeend', cardHtml);
  chat.scrollTop = chat.scrollHeight;
}

// Envía el mensaje al backend y maneja la respuesta
async function send() {
  const txt = input.value.trim();
  if (!txt) return;
  add('user', txt);
  input.value = '';
  add('assistant', '…');

  try {
    // Session ID persistente
    const sid = localStorage.getItem('sid') || String(Date.now());
    localStorage.setItem('sid', sid);

    const res = await fetch('/api/chatbot/send', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ message: txt, session_id: sid })
    });

    const json = await res.json();
    chat.lastChild.remove(); // quita “…”

    if (json.success) {
      // Mensaje del asistente
      add('assistant', json.data.response);

      // Tarjetas solo si hay contexto y restaurantes
      if (json.data.context_used && Array.isArray(json.data.context.restaurants)) {
        json.data.context.restaurants.forEach(r => addCard(r));
      }
    } else {
      add('assistant', 'Error en la respuesta del servidor');
    }
  } catch {
    chat.lastChild.remove();
    add('assistant', 'Error de red');
  }
}

// Evento: envío al hacer click o Enter
btn.onclick = send;
input.addEventListener('keyup', e => {
  if (e.key === 'Enter') send();
});

// Saludo inicial (solo una vez por sesión)
document.addEventListener('DOMContentLoaded', () => {
  if (!sessionStorage.getItem('welcomed')) {
    add('assistant',
      `<div class="alert alert-info py-1 mb-2">
         ¡Hola! Soy tu asistente de <strong>MiCiudadGourmet</strong>. ¿En qué puedo ayudarte hoy?
       </div>`
    );
    sessionStorage.setItem('welcomed','1');
  }
  input.focus();
});
</script>
@endpush
