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

/* 1. Escapamos HTML para evitar XSS  */
function escapeHtml (str) {
  return str.replace(/[&<>'"]/g, m =>
    ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[m])
  );
}

function add (role, text) {
  const div = document.createElement('div');
  div.className = role === 'user' ? 'text-end mb-2' : 'text-start mb-2';
  div.innerHTML = `<span class="badge ${role==='user'?'bg-primary':'bg-secondary'}">
                     ${escapeHtml(text)}
                   </span>`;
  chat.appendChild(div);
  chat.scrollTop = chat.scrollHeight;
}

function addCard (r) {
  /* 2. Mostramos categorías opcionalmente y evitamos <img> roto */
  const cat = r.categories?.length ? `<p><em>${r.categories.join(', ')}</em></p>` : '';
  const img = r.image ? `<img src="${r.image}" class="card-img-top" alt="${r.name}">` : '';
  chat.insertAdjacentHTML('beforeend', `
    <div class="card mb-2" style="max-width:300px">
      ${img}
      <div class="card-body">
        <h5 class="card-title">${escapeHtml(r.name)}</h5>
        ${cat}
        <p class="card-text">${escapeHtml(r.description)}</p>
        <p class="card-text"><small class="text-muted">${escapeHtml(r.address)}</small></p>
        <a href="/restaurants/${r.id}" target="_blank"
           class="btn btn-sm btn-outline-primary">Ver detalle</a>
      </div>
    </div>`);
  chat.scrollTop = chat.scrollHeight;
}

async function send () {
  const txt = input.value.trim();
  if (!txt) return;

  add('user', txt);
  input.value = '';
  add('assistant', '…');

  try {
    const sid = localStorage.getItem('sid') || String(Date.now());
    localStorage.setItem('sid', sid);

    const res  = await fetch('/api/chatbot/send', {
      method : 'POST',
      headers: {
        'Content-Type'  : 'application/json',
        'X-CSRF-TOKEN'  : document.querySelector('meta[name="csrf-token"]').content
      },
      body   : JSON.stringify({ message: txt, session_id: sid })
    });
    const json = await res.json();
    chat.lastChild.remove();           // quitamos el “…”

    if (json.success) {
      add('assistant', json.data.response);
      if (json.data.context_used && json.data.context?.restaurants) {
        json.data.context.restaurants.forEach(addCard);
      }
    } else {
      add('assistant', 'Error en la respuesta del servidor');
    }
  } catch {
    chat.lastChild.remove();
    add('assistant', 'Error de red');
  }
}

btn.onclick = send;
input.addEventListener('keyup', e => e.key === 'Enter' && send());

document.addEventListener('DOMContentLoaded', () => {

    add('assistant', '¡Hola! Soy tu asistente de MiCiudadGourmet. ¿En qué puedo ayudarte hoy?');

  input.focus();
});
</script>
@endpush
@endsection
