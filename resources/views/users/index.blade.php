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

<!-- Modal de Criar/Editar Utilizador -->
<div id="userModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Novo Utilizador</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="userForm" onsubmit="submitForm(event)">
            <input type="hidden" id="userId" name="user_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                <input type="text" id="name" name="name" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" id="email" name="email" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Telemóvel</label>
                <input type="tel" id="telefone" name="telefone" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Grupo de Permissões *</label>
                <select id="grupo_permissoes" name="grupo_permissoes" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Selecione...</option>
                    <option value="admin">Administrador</option>
                    <option value="gestor">Gestor</option>
                    <option value="operador">Operador</option>
                    <option value="visualizador">Visualizador</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado *</label>
                <select id="status" name="status" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                </select>
            </div>
            
            <div id="passwordFields">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Senha *</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            
            <div id="errorMessage" class="mb-4 text-red-600 text-sm hidden"></div>
            
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentUserId = null;
    
    // Função para obter CSRF token
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }
    
    // Carregar usuários
    function loadUsers() {
        fetch('/api/users', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('usersTable');
            if (!data.data || !data.data.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum utilizador encontrado</td></tr>';
                return;
            }
            
            tbody.innerHTML = data.data.map(user => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium">${escapeHtml(user.name)}</td>
                    <td class="px-6 py-4 text-sm">${escapeHtml(user.email)}</td>
                    <td class="px-6 py-4 text-sm">${user.telefone || '-'}</td>
                    <td class="px-6 py-4 text-sm">${getGrupoLabel(user.grupo_permissoes)}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs rounded-full ${user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${user.status === 'active' ? 'Ativo' : 'Inativo'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm space-x-2">
                        <button onclick="editUser(${user.id})" class="text-blue-600 hover:text-blue-800">Editar</button>
                        <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-800">Eliminar</button>
                    </td>
                </tr>
            `).join('');
        })
        .catch(error => {
            console.error('Erro ao carregar usuários:', error);
            document.getElementById('usersTable').innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Erro ao carregar usuários</td></tr>';
        });
    }
    
    // Mapear grupos para labels
    function getGrupoLabel(grupo) {
        const grupos = {
            'admin': 'Administrador',
            'gestor': 'Gestor',
            'operador': 'Operador',
            'visualizador': 'Visualizador'
        };
        return grupos[grupo] || grupo;
    }
    
    // Escapar HTML para evitar XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Abrir modal para criar usuário
    function openCreateModal() {
        currentUserId = null;
        document.getElementById('modalTitle').textContent = 'Novo Utilizador';
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('passwordFields').style.display = 'block';
        document.getElementById('password').required = true;
        document.getElementById('password_confirmation').required = true;
        document.getElementById('errorMessage').classList.add('hidden');
        document.getElementById('userModal').classList.remove('hidden');
    }
    
    // Editar usuário
    function editUser(id) {
        currentUserId = id;
        document.getElementById('modalTitle').textContent = 'Editar Utilizador';
        document.getElementById('errorMessage').classList.add('hidden');
        
        // Buscar dados do usuário
        fetch(`/api/users/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(user => {
            document.getElementById('userId').value = user.id;
            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('telefone').value = user.telefone || '';
            document.getElementById('grupo_permissoes').value = user.grupo_permissoes;
            document.getElementById('status').value = user.status;
            
            // Esconder campos de senha na edição
            document.getElementById('passwordFields').style.display = 'none';
            document.getElementById('password').required = false;
            document.getElementById('password_confirmation').required = false;
            
            document.getElementById('userModal').classList.remove('hidden');
        })
        .catch(error => {
            alert('Erro ao carregar dados do usuário');
        });
    }
    
    // Fechar modal
    function closeModal() {
        document.getElementById('userModal').classList.add('hidden');
        document.getElementById('userForm').reset();
        document.getElementById('errorMessage').classList.add('hidden');
    }
    
    // Submeter formulário
    function submitForm(event) {
        event.preventDefault();
        
        const userId = document.getElementById('userId').value;
        const isEditing = userId && userId !== '';
        
        // Coletar dados
        const formData = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            telefone: document.getElementById('telefone').value,
            grupo_permissoes: document.getElementById('grupo_permissoes').value,
            status: document.getElementById('status').value
        };
        
        // Adicionar senha apenas se for criação ou se foi preenchida na edição
        const password = document.getElementById('password').value;
        if (password) {
            formData.password = password;
            formData.password_confirmation = document.getElementById('password_confirmation').value;
        }
        
        const url = isEditing ? `/api/users/${userId}` : '/api/users';
        const method = isEditing ? 'PUT' : 'POST';
        
        // Mostrar loading
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Salvando...';
        submitBtn.disabled = true;
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json().then(data => ({ status: response.status, data })))
        .then(({ status, data }) => {
            if (status === 200 || status === 201) {
                // Sucesso
                closeModal();
                loadUsers();
                alert(isEditing ? 'Utilizador atualizado com sucesso!' : 'Utilizador criado com sucesso!');
            } else {
                // Erro de validação
                const errorDiv = document.getElementById('errorMessage');
                let errorMessage = '';
                
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join('\n');
                } else if (data.message) {
                    errorMessage = data.message;
                } else {
                    errorMessage = 'Erro ao salvar utilizador';
                }
                
                errorDiv.textContent = errorMessage;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = 'Erro de conexão. Tente novamente.';
            errorDiv.classList.remove('hidden');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }
    
    // Deletar usuário
    function deleteUser(id) {
        if (confirm('Tem certeza que deseja eliminar este utilizador?')) {
            fetch(`/api/users/${id}`, { 
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
                    loadUsers();
                    alert('Utilizador eliminado com sucesso!');
                } else {
                    alert('Erro ao eliminar utilizador');
                }
            })
            .catch(error => {
                alert('Erro ao eliminar utilizador');
            });
        }
    }
    
    // Fechar modal clicando fora
    document.getElementById('userModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    loadUsers();
</script>
@endsection