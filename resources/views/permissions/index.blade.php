@extends('layouts.app')

@section('title', 'Permissões')
@section('header', 'Gestão de Acessos - Permissões')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold">Grupos de Permissões</h2>
        <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            + Novo Grupo
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome do Grupo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilizadores Relacionados</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody id="rolesTable">
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Carregando...<\ /td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Grupo -->
<div id="roleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Novo Grupo</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form id="roleForm">
            @csrf
            <input type="hidden" id="roleId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nome do Grupo *</label>
                <input type="text" id="roleName" required class="w-full px-3 py-2 border rounded-md">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Permissões</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-60 overflow-y-auto border p-2 rounded" id="permissionsContainer">
                    <!-- Permissões serão carregadas via JS -->
                </div>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded-md">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentRoleId = null;

    async function loadRoles() {
        const response = await fetch('/api/permissions');
        const roles = await response.json();
        renderTable(roles);
    }

    function renderTable(roles) {
        const tbody = document.getElementById('rolesTable');
        if (!roles || roles.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Nenhum grupo encontrado<\/td></tr>';
            return;
        }
        let html = '';
        roles.forEach(role => {
            const statusClass = 'bg-green-100 text-green-800';
            const statusLabel = 'Ativo';
            const userCount = role.users ? role.users.length : 0;
            html += `<tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium">${role.name}<\/td>
                        <td class="px-6 py-4 text-sm">${userCount} utilizador(es)<\/td>
                        <td class="px-6 py-4 text-sm"><span class="px-2 py-1 text-xs rounded ${statusClass}">${statusLabel}<\/span><\/td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button onclick="editRole(${role.id})" class="text-blue-600 hover:text-blue-800">Editar<\/button>
                            <button onclick="deleteRole(${role.id})" class="text-red-600 hover:text-red-800">Eliminar<\/button>
                        <\/td>
                    <\/tr>`;
        });
        tbody.innerHTML = html;
    }

    async function loadPermissions() {
        const response = await fetch('/api/permissions-list');
        const permissions = await response.json();
        const container = document.getElementById('permissionsContainer');
        container.innerHTML = '';

        permissions.forEach(perm => {
            const div = document.createElement('div');
            div.className = 'flex items-center space-x-2';
            div.innerHTML = `<input type="checkbox" id="perm_${perm.id}" value="${perm.name}" class="rounded border-gray-300">
                        <label for="perm_${perm.id}" class="text-sm">${perm.name}</label>`;
            container.appendChild(div);
        });
    }

    function openCreateModal() {
        currentRoleId = null;
        document.getElementById('modalTitle').innerText = 'Novo Grupo';
        document.getElementById('roleForm').reset();
        document.getElementById('roleId').value = '';
        document.querySelectorAll('#permissionsContainer input[type="checkbox"]').forEach(cb => cb.checked = false);
        document.getElementById('roleModal').classList.remove('hidden');
    }

    async function editRole(id) {
        try {
            const response = await fetch(`/api/permissions/${id}`);
            const role = await response.json();

            currentRoleId = role.id;
            document.getElementById('modalTitle').innerText = 'Editar Grupo';
            document.getElementById('roleId').value = role.id;
            document.getElementById('roleName').value = role.name;

            const permNames = role.permissions ? role.permissions.map(p => p.name) : [];
            document.querySelectorAll('#permissionsContainer input[type="checkbox"]').forEach(cb => {
                cb.checked = permNames.includes(cb.value);
            });

            document.getElementById('roleModal').classList.remove('hidden');
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao carregar dados do grupo');
        }
    }

    async function deleteRole(id) {
        if (!confirm('Tem certeza que deseja eliminar este grupo?')) return;
        const response = await fetch(`/api/permissions/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            loadRoles();
        } else {
            alert('Erro: ' + result.message);
        }
    }

    document.getElementById('roleForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('roleId').value;
        const url = id ? `/api/permissions/${id}` : '/api/permissions';
        const method = id ? 'PUT' : 'POST';
        const permissions = Array.from(document.querySelectorAll('#permissionsContainer input[type="checkbox"]:checked')).map(cb => cb.value);
        const data = {
            name: document.getElementById('roleName').value,
            permissions
        };
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            closeModal();
            loadRoles();
            alert(result.message);
        } else {
            alert('Erro: ' + result.message);
        }
    });

    function closeModal() {
        document.getElementById('roleModal').classList.add('hidden');
    }

    loadPermissions();
    loadRoles();
</script>
@endsection