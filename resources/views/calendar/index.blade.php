@extends('layouts.app')

@section('title', 'Calendário')
@section('header', 'Calendário')

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex justify-between items-center mb-4">
        <div class="flex space-x-2">
            <select id="filterUser" class="px-3 py-1 border rounded text-sm">
                <option value="">Todos os Utilizadores</option>
            </select>
            <select id="filterEntity" class="px-3 py-1 border rounded text-sm">
                <option value="">Todas as Entidades</option>
            </select>
            <button onclick="refreshCalendar()" class="px-3 py-1 bg-blue-500 text-white rounded text-sm">Filtrar</button>
        </div>
        <button onclick="openEventModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            + Novo Evento
        </button>
    </div>
    
    <div id="calendar"></div>
</div>

<!-- Modal de Evento -->
<div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Novo Evento</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        
        <form id="eventForm">
            @csrf
            <input type="hidden" id="eventId">
            
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                <input type="text" id="title" required class="w-full px-3 py-2 border rounded-md">
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data *</label>
                    <input type="date" id="date" required class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hora *</label>
                    <input type="time" id="time" required class="w-full px-3 py-2 border rounded-md">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duração (minutos)</label>
                    <input type="number" id="duration" value="60" class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select id="type_id" required class="w-full px-3 py-2 border rounded-md">
                        <option value="">Selecione</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ação *</label>
                    <select id="action_id" required class="w-full px-3 py-2 border rounded-md">
                        <option value="">Selecione</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Entidade</label>
                    <select id="entity_id" class="w-full px-3 py-2 border rounded-md">
                        <option value="">Selecione</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Partilha</label>
                    <select id="share" class="w-full px-3 py-2 border rounded-md">
                        <option value="private">Privado</option>
                        <option value="internal">Interno</option>
                        <option value="public">Público</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Conhecimento</label>
                    <select id="knowledge" class="w-full px-3 py-2 border rounded-md">
                        <option value="low">Baixo</option>
                        <option value="medium">Médio</option>
                        <option value="high">Alto</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                <textarea id="description" rows="3" class="w-full px-3 py-2 border rounded-md"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select id="status" class="w-full px-3 py-2 border rounded-md">
                    <option value="scheduled">Agendado</option>
                    <option value="in_progress">Em andamento</option>
                    <option value="completed">Concluído</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded-md">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Salvar</button>
            </div>
        </form>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
let calendar = null;

function loadFilters() {
    // Carregar utilizadores
    fetch('/api/users')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('filterUser');
            const users = data.data || [];
            users.forEach(user => {
                select.innerHTML += `<option value="${user.id}">${user.name}</option>`;
            });
        });
    
    // Carregar entidades
    fetch('/api/entities')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('filterEntity');
            const entities = data.data || [];
            entities.forEach(entity => {
                select.innerHTML += `<option value="${entity.id}">${entity.name}</option>`;
            });
            
            const selectEntity = document.getElementById('entity_id');
            selectEntity.innerHTML = '<option value="">Selecione</option>';
            entities.forEach(entity => {
                selectEntity.innerHTML += `<option value="${entity.id}">${entity.name}</option>`;
            });
        });
    
    // Carregar tipos
    fetch('/api/calendar/types')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('type_id');
            data.forEach(type => {
                select.innerHTML += `<option value="${type.id}">${type.name}</option>`;
            });
        });
    
    // Carregar ações
    fetch('/api/calendar/actions')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('action_id');
            data.forEach(action => {
                select.innerHTML += `<option value="${action.id}">${action.name}</option>`;
            });
        });
}

function loadEvents() {
    const userId = document.getElementById('filterUser').value;
    const entityId = document.getElementById('filterEntity').value;
    
    let url = '/api/calendar/events';
    const params = [];
    if (userId) params.push(`user_id=${userId}`);
    if (entityId) params.push(`entity_id=${entityId}`);
    if (params.length) url += '?' + params.join('&');
    
    fetch(url)
        .then(response => response.json())
        .then(events => {
            if (calendar) {
                calendar.removeAllEvents();
                calendar.addEventSource(events.map(event => ({
                    id: event.id,
                    title: event.title,
                    start: event.start_datetime,
                    end: event.end_datetime,
                    backgroundColor: event.type?.color || '#3B82F6',
                    borderColor: event.type?.color || '#3B82F6',
                    extendedProps: {
                        description: event.description,
                        type: event.type?.name,
                        action: event.action?.name,
                        entity: event.entity?.name
                    }
                })));
            }
        });
}

function refreshCalendar() {
    loadEvents();
}

function initCalendar() {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'pt',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia'
        },
        events: [],
        eventClick: function(info) {
            editEvent(info.event.id);
        },
        dateClick: function(info) {
            const date = new Date(info.date);
            document.getElementById('date').value = date.toISOString().split('T')[0];
            document.getElementById('time').value = '09:00';
            openEventModal();
        }
    });
    calendar.render();
}

function openEventModal() {
    document.getElementById('modalTitle').textContent = 'Novo Evento';
    document.getElementById('eventForm').reset();
    document.getElementById('eventId').value = '';
    document.getElementById('status').value = 'scheduled';
    document.getElementById('share').value = 'private';
    document.getElementById('knowledge').value = 'medium';
    document.getElementById('eventModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('eventModal').classList.add('hidden');
}

function editEvent(id) {
    fetch(`/api/calendar/events/${id}`)
        .then(response => response.json())
        .then(event => {
            document.getElementById('modalTitle').textContent = 'Editar Evento';
            document.getElementById('eventId').value = event.id;
            document.getElementById('title').value = event.title;
            document.getElementById('description').value = event.description || '';
            document.getElementById('type_id').value = event.type_id;
            document.getElementById('action_id').value = event.action_id;
            document.getElementById('entity_id').value = event.entity_id || '';
            document.getElementById('share').value = event.share || 'private';
            document.getElementById('knowledge').value = event.knowledge || 'medium';
            document.getElementById('status').value = event.status || 'scheduled';
            document.getElementById('duration').value = event.duration_minutes || 60;
            
            const startDate = new Date(event.start_datetime);
            document.getElementById('date').value = startDate.toISOString().split('T')[0];
            document.getElementById('time').value = startDate.toTimeString().slice(0,5);
            
            document.getElementById('eventModal').classList.remove('hidden');
        });
}

function saveEvent(event) {
    event.preventDefault();
    
    const id = document.getElementById('eventId').value;
    const url = id ? `/api/calendar/events/${id}` : '/api/calendar/events';
    const method = id ? 'PUT' : 'POST';
    
    const date = document.getElementById('date').value;
    const time = document.getElementById('time').value;
    const duration = parseInt(document.getElementById('duration').value);
    
    const startDateTime = new Date(`${date}T${time}:00`);
    const endDateTime = new Date(startDateTime.getTime() + duration * 60000);
    
    const data = {
        title: document.getElementById('title').value,
        description: document.getElementById('description').value,
        start_datetime: startDateTime.toISOString(),
        end_datetime: endDateTime.toISOString(),
        duration_minutes: duration,
        type_id: parseInt(document.getElementById('type_id').value),
        action_id: parseInt(document.getElementById('action_id').value),
        entity_id: document.getElementById('entity_id').value ? parseInt(document.getElementById('entity_id').value) : null,
        share: document.getElementById('share').value,
        knowledge: document.getElementById('knowledge').value,
        status: document.getElementById('status').value
    };
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success || result.event) {
            closeModal();
            loadEvents();
            alert('Evento salvo com sucesso!');
        } else {
            alert('Erro: ' + (result.message || JSON.stringify(result)));
        }
    })
    .catch(error => alert('Erro ao salvar: ' + error.message));
}

document.getElementById('eventForm').addEventListener('submit', saveEvent);

// Inicializar
loadFilters();
initCalendar();
loadEvents();
</script>
@endsection