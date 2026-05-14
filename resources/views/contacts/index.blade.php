@extends('layouts.app')

@section('title', 'Contactos')
@section('header', 'Contactos')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800">Lista de Contactos</h2>
        <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            + Novo Contacto
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Apelido</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Função</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entidade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody id="contactsTable">
                <!-- Dados carregados via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="contactModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium" id="modalTitle">Novo Contacto</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        
        <form id="contactForm">
            <input type="hidden" id="contactId" name="contactId">
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Entidade *</label>
                <select id="entity_id" name="entity_id" required class="w-full px-3 py-2 border rounded">
                    <option value="">Carregando...</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Primeiro Nome *</label>
                <input type="text" id="first_name" name="first_name" required class="w-full px-3 py-2 border rounded">
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Último Nome *</label>
                <input type="text" id="last_name" name="last_name" required class="w-full px-3 py-2 border rounded">
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Função</label>
                <input type="text" id="role" name="role" class="w-full px-3 py-2 border rounded">
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Telefone</label>
                <input type="text" id="phone" name="phone" class="w-full px-3 py-2 border rounded">
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" id="email" name="email" class="w-full px-3 py-2 border rounded">
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded">Cancelar</button>
                <button type="button" onclick="saveContact()" class="px-4 py-2 bg-indigo-600 text-white rounded">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentEditId = null;

function loadEntities() {
    fetch('/api/entities/clients')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('entity_id');
            select.innerHTML = '<option value="">Selecione uma entidade</option>';
            const entities = data.data || [];
            entities.forEach(entity => {
                select.innerHTML += `<option value="${entity.id}">${entity.name}</option>`;
            });
        })
        .catch(error => console.error('Erro:', error));
}

function loadContacts() {
    fetch('/api/contacts')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('contactsTable');
            const contacts = data.data || data;
            
            if (!contacts || contacts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center">Nenhum contacto encontrado</td></tr>';
                return;
            }
            
            let html = '';
            contacts.forEach(contact => {
                html += `<tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm">${contact.first_name || '-'}</td>
                    <td class="px-6 py-4 text-sm">${contact.last_name || '-'}</td>
                    <td class="px-6 py-4 text-sm">${contact.role || '-'}</td>
                    <td class="px-6 py-4 text-sm">${contact.entity?.name || '-'}</td>
                    <td class="px-6 py-4 text-sm">${contact.phone || '-'}</td>
                    <td class="px-6 py-4 text-sm">${contact.email || '-'}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs rounded ${contact.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${contact.is_active ? 'Ativo' : 'Inativo'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm space-x-2">
                        <button onclick="openEditModal(${contact.id})" class="text-blue-600 hover:text-blue-800">Editar</button>
                        <button onclick="deleteContact(${contact.id})" class="text-red-600 hover:text-red-800">Eliminar</button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
        })
        .catch(error => console.error('Erro:', error));
}

function openCreateModal() {
    currentEditId = null;
    document.getElementById('modalTitle').innerText = 'Novo Contacto';
    document.getElementById('contactForm').reset();
    document.getElementById('contactId').value = '';
    document.getElementById('entity_id').value = '';
    document.getElementById('contactModal').classList.remove('hidden');
}

function openEditModal(id) {
    currentEditId = id;
    document.getElementById('modalTitle').innerText = 'Editar Contacto';
    
    fetch(`/api/contacts/${id}`)
        .then(response => response.json())
        .then(contact => {
            document.getElementById('contactId').value = contact.id;
            document.getElementById('entity_id').value = contact.entity_id;
            document.getElementById('first_name').value = contact.first_name;
            document.getElementById('last_name').value = contact.last_name;
            document.getElementById('role').value = contact.role || '';
            document.getElementById('phone').value = contact.phone || '';
            document.getElementById('email').value = contact.email || '';
            document.getElementById('contactModal').classList.remove('hidden');
        })
        .catch(error => console.error('Erro:', error));
}

function closeModal() {
    document.getElementById('contactModal').classList.add('hidden');
    currentEditId = null;
}

function saveContact() {
    const id = document.getElementById('contactId').value;
    const url = id ? `/api/contacts/${id}` : '/api/contacts';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        entity_id: document.getElementById('entity_id').value,
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        role: document.getElementById('role').value,
        phone: document.getElementById('phone').value,
        email: document.getElementById('email').value,
        is_active: 1
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
        if (result.success || result.message || result.contact) {
            closeModal();
            loadContacts();
            alert('Contacto salvo com sucesso!');
        } else {
            alert('Erro: ' + JSON.stringify(result));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar: ' + error.message);
    });
}

function deleteContact(id) {
    if (confirm('Tem certeza que deseja eliminar este contacto?')) {
        fetch(`/api/contacts/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        }).then(() => loadContacts());
    }
}

// Inicializar
loadEntities();
loadContacts();
</script>
@endsection