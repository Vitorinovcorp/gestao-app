@extends('layouts.app')

@section('title', 'Artigos')
@section('header', 'Artigos')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800">Lista de Artigos</h2>
        <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            + Novo Artigo
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referência</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preço</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IVA</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody id="articlesTable" class="divide-y divide-gray-200">
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Carregando...</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t border-gray-200" id="pagination"></div>
</div>

<!-- Modal de Criar/Editar Artigo -->
<div id="articleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Novo Artigo</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        
        <form id="articleForm">
            <input type="hidden" id="articleId" name="id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Referência</label>
                <input type="text" id="reference" name="reference" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nome</label>
                <input type="text" id="name" name="name" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                <textarea id="description" name="description" rows="3"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Preço (€)</label>
                    <input type="number" step="0.01" id="price" name="price" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Custo (€)</label>
                    <input type="number" step="0.01" id="cost_price" name="cost_price"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">IVA</label>
                    <select id="vat_id" name="vat_id" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Selecione</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stock</label>
                    <input type="number" id="stock_current" name="stock_current" value="0"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" class="mr-2" checked>
                    <span class="text-sm text-gray-700">Ativo</span>
                </label>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancelar</button>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPage = 1;
let vatRates = [];

// Carregar artigos
function loadArticles() {
    fetch(`/api/articles?page=${currentPage}`)
        .then(response => response.json())
        .then(data => {
            renderTable(data.data);
            renderPagination(data);
        });
}

// Carregar taxas de IVA
function loadVatRates() {
    fetch('/api/financial/vat-rates')
        .then(response => response.json())
        .then(data => {
            vatRates = data;
            const select = document.getElementById('vat_id');
            select.innerHTML = '<option value="">Selecione o IVA</option>';
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(vat => {
                    select.innerHTML += `<option value="${vat.id}">${vat.name} (${vat.rate}%)</option>`;
                });
            } else {
                // Fallback: carregar via GET normal
                fetch('/vat-rates')
                    .then(res => res.json())
                    .then(fallbackData => {
                        fallbackData.forEach(vat => {
                            select.innerHTML += `<option value="${vat.id}">${vat.name} (${vat.rate}%)</option>`;
                        });
                    });
            }
        })
        .catch(error => {
            console.error('Erro ao carregar IVA:', error);
            // Dados de fallback
            const select = document.getElementById('vat_id');
            select.innerHTML = '<option value="1">Normal (23%)</option><option value="2">Intermediário (13%)</option><option value="3">Reduzido (6%)</option><option value="4">Isento (0%)</option>';
        });
}

function renderTable(articles) {
    const tbody = document.getElementById('articlesTable');
    if (!articles || articles.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Nenhum artigo encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = articles.map(article => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm font-mono">${article.reference}</td>
            <td class="px-6 py-4 text-sm font-medium">${article.name}</td>
            <td class="px-6 py-4 text-sm">€ ${parseFloat(article.price).toFixed(2)}</td>
            <td class="px-6 py-4 text-sm">${article.vat?.rate || 0}%</td>
            <td class="px-6 py-4 text-sm">${article.stock_current || 0}</td>
            <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 text-xs rounded ${article.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${article.is_active ? 'Ativo' : 'Inativo'}
                </span>
            </td>
            <td class="px-6 py-4 text-sm space-x-2">
                <button onclick="editArticle(${article.id})" class="text-blue-600 hover:text-blue-800">Editar</button>
                <button onclick="deleteArticle(${article.id})" class="text-red-600 hover:text-red-800">Eliminar</button>
            </td>
        </tr>
    `).join('');
}

function renderPagination(data) {
    const pagination = document.getElementById('pagination');
    if (data.last_page <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '<div class="flex justify-between items-center">';
    html += '<div class="text-sm text-gray-500">Mostrando ' + data.from + ' a ' + data.to + ' de ' + data.total + '</div>';
    html += '<div class="flex space-x-1">';
    
    if (data.current_page > 1) {
        html += `<button onclick="goToPage(${data.current_page - 1})" class="px-3 py-1 border rounded hover:bg-gray-50">Anterior</button>`;
    }
    
    for (let i = 1; i <= data.last_page; i++) {
        if (i === data.current_page) {
            html += `<button class="px-3 py-1 bg-indigo-600 text-white rounded">${i}</button>`;
        } else if (Math.abs(i - data.current_page) <= 2) {
            html += `<button onclick="goToPage(${i})" class="px-3 py-1 border rounded hover:bg-gray-50">${i}</button>`;
        }
    }
    
    if (data.current_page < data.last_page) {
        html += `<button onclick="goToPage(${data.current_page + 1})" class="px-3 py-1 border rounded hover:bg-gray-50">Próximo</button>`;
    }
    
    html += '</div></div>';
    pagination.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    loadArticles();
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Novo Artigo';
    document.getElementById('articleForm').reset();
    document.getElementById('articleId').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('articleModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('articleModal').classList.add('hidden');
}

function editArticle(id) {
    fetch(`/api/articles/${id}`)
        .then(response => response.json())
        .then(article => {
            document.getElementById('modalTitle').textContent = 'Editar Artigo';
            document.getElementById('articleId').value = article.id;
            document.getElementById('reference').value = article.reference;
            document.getElementById('name').value = article.name;
            document.getElementById('description').value = article.description || '';
            document.getElementById('price').value = article.price;
            document.getElementById('cost_price').value = article.cost_price || '';
            document.getElementById('vat_id').value = article.vat_id;
            document.getElementById('stock_current').value = article.stock_current || 0;
            document.getElementById('is_active').checked = article.is_active;
            document.getElementById('articleModal').classList.remove('hidden');
        });
}

function deleteArticle(id) {
    if (confirm('Tem certeza que deseja eliminar este artigo?')) {
        fetch(`/api/articles/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(() => loadArticles());
    }
}

document.getElementById('articleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('articleId').value;
    const url = id ? `/api/articles/${id}` : '/api/articles';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        reference: document.getElementById('reference').value,
        name: document.getElementById('name').value,
        description: document.getElementById('description').value,
        price: parseFloat(document.getElementById('price').value),
        cost_price: parseFloat(document.getElementById('cost_price').value) || null,
        vat_id: parseInt(document.getElementById('vat_id').value),
        stock_current: parseInt(document.getElementById('stock_current').value) || 0,
        is_active: document.getElementById('is_active').checked
    };
    
    console.log('Enviando:', data); // Para debug
    
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
        console.log('Resposta:', result);
        if (result.message || result.id || result.article) {
            closeModal();
            loadArticles();
            alert('Artigo salvo com sucesso!');
        } else {
            alert('Erro: ' + JSON.stringify(result));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar artigo. Verifique o console.');
    });
});

// Inicializar
loadVatRates();
loadArticles();
</script>
@endsection