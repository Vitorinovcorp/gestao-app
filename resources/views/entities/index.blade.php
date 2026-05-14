@extends('layouts.app')

@section('title', 'Clientes e Fornecedores')
@section('header', 'Clientes e Fornecedores')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex space-x-2">
            <button onclick="filterType('client')" class="px-3 py-1 bg-blue-500 text-white rounded text-sm">Clientes</button>
            <button onclick="filterType('supplier')" class="px-3 py-1 bg-green-500 text-white rounded text-sm">Fornecedores</button>
            <button onclick="filterType('both')" class="px-3 py-1 bg-gray-500 text-white rounded text-sm">Todos</button>
            <button onclick="loadEntities()" class="px-3 py-1 bg-yellow-500 text-white rounded text-sm">Recarregar</button>
        </div>
        <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            + Nova Entidade
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIF</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody id="entitiesTable">
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500" id="loadingMsg">Carregando...</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t border-gray-200" id="pagination"></div>
</div>

<!-- Modal de Criar/Editar Entidade -->
<div id="entityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Nova Entidade</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        
        <form id="entityForm" onsubmit="saveEntity(event)">
            <input type="hidden" id="entityId" name="id">
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo *</label>
                    <select id="type" name="type" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="client">Cliente</option>
                        <option value="supplier">Fornecedor</option>
                        <option value="both">Ambos</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">NIF *</label>
                    <input type="text" id="nif" name="nif" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nome *</label>
                <input type="text" id="name" name="name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Morada</label>
                <input type="text" id="address" name="address" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código Postal</label>
                    <input type="text" id="postal_code" name="postal_code" placeholder="1000-001" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Localidade</label>
                    <input type="text" id="city" name="city" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                    <input type="text" id="phone" name="phone" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telemóvel</label>
                    <input type="text" id="mobile" name="mobile" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Website</label>
                    <input type="text" id="website" name="website" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" class="mr-2" checked>
                    <span class="text-sm text-gray-700">Ativo</span>
                </label>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentType = 'client';
let currentPage = 1;

function loadEntities() {
    let url = '';
    if (currentType === 'client') {
        url = '/api/entities/clients?page=' + currentPage;
    } else if (currentType === 'supplier') {
        url = '/api/entities/suppliers?page=' + currentPage;
    } else {
        url = '/api/entities?page=' + currentPage;
    }
    
    document.getElementById('entitiesTable').innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Carregando...</td></tr>';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.data && data.data.length > 0) {
                renderTable(data.data);
                renderPagination(data);
            } else {
                document.getElementById('entitiesTable').innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Nenhuma entidade encontrada</td></tr>';
                document.getElementById('pagination').innerHTML = '';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('entitiesTable').innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Erro ao carregar dados</td></tr>';
        });
}

function renderTable(entities) {
    let html = '';
    for (let i = 0; i < entities.length; i++) {
        const entity = entities[i];
        const typeLabel = entity.type === 'client' ? 'Cliente' : (entity.type === 'supplier' ? 'Fornecedor' : 'Ambos');
        const typeColor = entity.type === 'client' ? 'bg-blue-100 text-blue-800' : (entity.type === 'supplier' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800');
        const statusColor = entity.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        const statusLabel = entity.is_active ? 'Ativo' : 'Inativo';
        
        html += `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm">${entity.nif || '-'}</td>
                <td class="px-6 py-4 text-sm font-medium">${entity.name}</td>
                <td class="px-6 py-4 text-sm">${entity.phone || entity.mobile || '-'}</td>
                <td class="px-6 py-4 text-sm">${entity.email || '-'}</td>
                <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 text-xs rounded ${typeColor}">${typeLabel}</span>
                </td>
                <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 text-xs rounded ${statusColor}">${statusLabel}</span>
                </td>
                <td class="px-6 py-4 text-sm space-x-2">
                    <button onclick="editEntity(${entity.id})" class="text-blue-600 hover:text-blue-800">Editar</button>
                    <button onclick="deleteEntity(${entity.id})" class="text-red-600 hover:text-red-800">Eliminar</button>
                </td>
            </tr>
        `;
    }
    document.getElementById('entitiesTable').innerHTML = html;
}

function renderPagination(data) {
    if (!data || data.last_page <= 1) {
        document.getElementById('pagination').innerHTML = '';
        return;
    }
    
    let html = '<div class="flex justify-between items-center">';
    html += '<div class="text-sm text-gray-500">Mostrando ' + (data.from || 0) + ' a ' + (data.to || 0) + ' de ' + data.total + '</div>';
    html += '<div class="flex space-x-1">';
    
    if (data.current_page > 1) {
        html += `<button onclick="goToPage(${data.current_page - 1})" class="px-3 py-1 border rounded hover:bg-gray-50">Anterior</button>`;
    }
    
    html += `<button class="px-3 py-1 bg-indigo-600 text-white rounded">${data.current_page}</button>`;
    
    if (data.current_page < data.last_page) {
        html += `<button onclick="goToPage(${data.current_page + 1})" class="px-3 py-1 border rounded hover:bg-gray-50">Próximo</button>`;
    }
    
    html += '</div></div>';
    document.getElementById('pagination').innerHTML = html;
}

function filterType(type) {
    currentType = type;
    currentPage = 1;
    loadEntities();
}

function goToPage(page) {
    currentPage = page;
    loadEntities();
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Nova Entidade';
    document.getElementById('entityForm').reset();
    document.getElementById('entityId').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('entityModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('entityModal').classList.add('hidden');
}

function editEntity(id) {
    fetch(`/api/entities/${id}`)
        .then(response => response.json())
        .then(entity => {
            document.getElementById('modalTitle').textContent = 'Editar Entidade';
            document.getElementById('entityId').value = entity.id;
            document.getElementById('type').value = entity.type;
            document.getElementById('nif').value = entity.nif;
            document.getElementById('name').value = entity.name;
            document.getElementById('address').value = entity.address || '';
            document.getElementById('postal_code').value = entity.postal_code || '';
            document.getElementById('city').value = entity.city || '';
            document.getElementById('phone').value = entity.phone || '';
            document.getElementById('mobile').value = entity.mobile || '';
            document.getElementById('email').value = entity.email || '';
            document.getElementById('website').value = entity.website || '';
            document.getElementById('is_active').checked = entity.is_active;
            document.getElementById('entityModal').classList.remove('hidden');
        });
}

function deleteEntity(id) {
    if (confirm('Tem certeza que deseja eliminar esta entidade?')) {
        fetch(`/api/entities/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        }).then(() => loadEntities());
    }
}

function saveEntity(event) {
    event.preventDefault();
    
    const id = document.getElementById('entityId').value;
    const url = id ? `/api/entities/${id}` : '/api/entities';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        type: document.getElementById('type').value,
        nif: document.getElementById('nif').value,
        name: document.getElementById('name').value,
        address: document.getElementById('address').value,
        postal_code: document.getElementById('postal_code').value,
        city: document.getElementById('city').value,
        phone: document.getElementById('phone').value,
        mobile: document.getElementById('mobile').value,
        email: document.getElementById('email').value,
        website: document.getElementById('website').value,
        is_active: document.getElementById('is_active').checked ? 1 : 0
    };
    
    console.log('Enviando dados:', data);
    console.log('URL:', url);
    console.log('Método:', method);
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
            });
        }
        return response.json();
    })
    .then(result => {
        console.log('Resposta:', result);
        if (result.success || result.message || result.entity) {
            closeModal();
            loadEntities();
            alert('Entidade salva com sucesso!');
        } else {
            alert('Erro: ' + (result.message || JSON.stringify(result)));
        }
    })
    .catch(error => {
        console.error('Erro detalhado:', error);
        alert('Erro ao salvar: ' + error.message);
    });
}

loadEntities();
</script>
@endsection