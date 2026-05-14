@extends('layouts.app')

@section('title', 'Utilizadores')
@section('header', 'Utilizadores')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200 flex justify-end">
        <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            + Novo Utilizador
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telemóvel</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grupo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody id="usersTable" class="divide-y divide-gray-200">
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Carregando...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    function loadUsers() {
        fetch('/api/users')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('usersTable');
                if (!data.data || !data.data.length) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum utilizador encontrado</td></tr>';
                    return;
                }
                
                tbody.innerHTML = data.data.map(user => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium">${user.name}</td>
                        <td class="px-6 py-4 text-sm">${user.email}</td>
                        <td class="px-6 py-4 text-sm">${user.phone || '-'}</td>
                        <td class="px-6 py-4 text-sm">${user.roles?.map(r => r.name).join(', ') || '-'}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 text-xs rounded ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${user.is_active ? 'Ativo' : 'Inativo'}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button onclick="editUser(${user.id})" class="text-blue-600 hover:text-blue-800">Editar</button>
                            <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>
                `).join('');
            });
    }

    function openCreateModal() {
        alert('Funcionalidade de criação - Implementar modal com formulário');
    }

    function editUser(id) {
        alert(`Editar utilizador ${id}`);
    }

    function deleteUser(id) {
        if (confirm('Tem certeza que deseja eliminar este utilizador?')) {
            fetch(`/api/users/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(() => loadUsers());
        }
    }

    loadUsers();
</script>
@endsection