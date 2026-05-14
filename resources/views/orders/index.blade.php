@extends('layouts.app')

@section('title', 'Encomendas')
@section('header', 'Encomendas')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex space-x-2">
            <select id="statusFilter" onchange="filterByStatus()" class="px-3 py-1 border rounded text-sm">
                <option value="">Todos os status</option>
                <option value="draft">Rascunho</option>
                <option value="confirmed">Confirmada</option>
                <option value="processing">Processamento</option>
                <option value="shipped">Enviada</option>
                <option value="delivered">Entregue</option>
                <option value="cancelled">Cancelada</option>
            </select>
        </div>
        <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            + Nova Encomenda
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody id="ordersTable">
                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Carregando...<\/td></tr>
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t border-gray-200" id="pagination"></div>
</div>

<!-- Modal de Criar Encomenda -->
<div id="orderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Nova Encomenda</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        
        <form id="orderForm" onsubmit="saveOrder(event)">
            <input type="hidden" id="orderId">
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                    <select id="client_id" required class="w-full px-3 py-2 border rounded-md">
                        <option value="">Selecione um cliente</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Entrega Prevista</label>
                    <input type="date" id="expected_delivery" class="w-full px-3 py-2 border rounded-md">
                </div>
            </div>
            
            <div class="mb-4">
                <h4 class="text-md font-semibold mb-2">Itens da Encomenda</h4>
                <div id="linesContainer" class="space-y-2">
                    <div class="line-item grid grid-cols-12 gap-2 items-center">
                        <div class="col-span-5">
                            <select name="article_id" class="article-select w-full px-2 py-1 border rounded text-sm" required>
                                <option value="">Selecione um artigo</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <input type="number" name="quantity" placeholder="Qtd" value="1" class="w-full px-2 py-1 border rounded text-sm" required>
                        </div>
                        <div class="col-span-3">
                            <input type="number" name="unit_price" placeholder="Preço" step="0.01" class="w-full px-2 py-1 border rounded text-sm" required>
                        </div>
                        <div class="col-span-1">
                            <button type="button" onclick="removeLine(this)" class="text-red-500 hover:text-red-700">✗</button>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addLine()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Adicionar linha</button>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea id="notes" rows="3" class="w-full px-3 py-2 border rounded-md"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPage = 1;
let currentStatus = '';
let articles = [];

function loadClients() {
    fetch('/api/entities/clients')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('client_id');
            const clients = data.data || [];
            select.innerHTML = '<option value="">Selecione um cliente</option>';
            clients.forEach(client => {
                select.innerHTML += `<option value="${client.id}">${client.name} (${client.nif})</option>`;
            });
        })
        .catch(error => console.error('Erro ao carregar clientes:', error));
}

function loadArticles() {
    fetch('/api/articles')
        .then(response => response.json())
        .then(data => {
            articles = data.data || [];
            refreshArticleSelects();
        })
        .catch(error => console.error('Erro ao carregar artigos:', error));
}

function refreshArticleSelects() {
    const selects = document.querySelectorAll('.article-select');
    selects.forEach(select => {
        const currentValue = select.value;
        select.innerHTML = '<option value="">Selecione um artigo</option>';
        articles.forEach(article => {
            select.innerHTML += `<option value="${article.id}" data-price="${article.price}">${article.reference} - ${article.name} (€${article.price})</option>`;
        });
        if (currentValue) select.value = currentValue;
    });
}

function addLine() {
    const container = document.getElementById('linesContainer');
    const newLine = document.createElement('div');
    newLine.className = 'line-item grid grid-cols-12 gap-2 items-center';
    newLine.innerHTML = `
        <div class="col-span-5">
            <select name="article_id" class="article-select w-full px-2 py-1 border rounded text-sm" required>
                <option value="">Selecione um artigo</option>
            </select>
        </div>
        <div class="col-span-2">
            <input type="number" name="quantity" placeholder="Qtd" value="1" class="w-full px-2 py-1 border rounded text-sm" required>
        </div>
        <div class="col-span-3">
            <input type="number" name="unit_price" placeholder="Preço" step="0.01" class="w-full px-2 py-1 border rounded text-sm" required>
        </div>
        <div class="col-span-1">
            <button type="button" onclick="removeLine(this)" class="text-red-500 hover:text-red-700">✗</button>
        </div>
    `;
    container.appendChild(newLine);
    
    const select = newLine.querySelector('.article-select');
    select.innerHTML = '<option value="">Selecione um artigo</option>';
    articles.forEach(article => {
        select.innerHTML += `<option value="${article.id}" data-price="${article.price}">${article.reference} - ${article.name} (€${article.price})</option>`;
    });
    
    select.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        if (price) {
            const priceInput = this.closest('.line-item').querySelector('input[name="unit_price"]');
            priceInput.value = price;
        }
    });
}

function removeLine(button) {
    const container = document.getElementById('linesContainer');
    if (container.children.length > 1) {
        button.closest('.line-item').remove();
    } else {
        alert('É necessário pelo menos uma linha');
    }
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Nova Encomenda';
    document.getElementById('orderForm').reset();
    document.getElementById('orderId').value = '';
    document.getElementById('expected_delivery').value = '';
    
    const container = document.getElementById('linesContainer');
    container.innerHTML = '';
    addLine();
    
    document.getElementById('orderModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('orderModal').classList.add('hidden');
}

function saveOrder(event) {
    event.preventDefault();
    
    const id = document.getElementById('orderId').value;
    const url = id ? `/api/orders/${id}` : '/api/orders';
    const method = id ? 'PUT' : 'POST';
    
    const lines = [];
    document.querySelectorAll('.line-item').forEach(line => {
        const articleSelect = line.querySelector('.article-select');
        const quantity = line.querySelector('input[name="quantity"]').value;
        const unitPrice = line.querySelector('input[name="unit_price"]').value;
        
        if (articleSelect.value && quantity && unitPrice) {
            lines.push({
                article_id: articleSelect.value,
                quantity: parseInt(quantity),
                unit_price: parseFloat(unitPrice)
            });
        }
    });
    
    if (lines.length === 0) {
        alert('Adicione pelo menos um item à encomenda');
        return;
    }
    
    const data = {
        client_id: document.getElementById('client_id').value,
        expected_delivery: document.getElementById('expected_delivery').value,
        lines: lines,
        notes: document.getElementById('notes').value
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
        if (result.success || result.message || result.order) {
            closeModal();
            loadOrders();
            alert('Encomenda salva com sucesso!');
        } else {
            alert('Erro: ' + JSON.stringify(result));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar: ' + error.message);
    });
}

function loadOrders() {
    let url = `/api/orders?page=${currentPage}`;
    if (currentStatus) url += `&status=${currentStatus}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            renderTable(data.data);
            renderPagination(data);
        })
        .catch(error => console.error('Erro ao carregar encomendas:', error));
}

function renderTable(orders) {
    const tbody = document.getElementById('ordersTable');
    if (!orders || orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhuma encomenda encontrada<\/td></tr>';
        return;
    }
    
    let html = '';
    orders.forEach(order => {
        const statusClass = getStatusClass(order.status);
        const statusLabel = getStatusLabel(order.status);
        
        html += `<tr class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm">${order.order_date || '-'}<\/td>
            <td class="px-6 py-4 text-sm font-mono font-medium">${order.number}<\/td>
            <td class="px-6 py-4 text-sm">${order.client?.name || '-'}<\/td>
            <td class="px-6 py-4 text-sm font-semibold">€ ${(Math.round(order.total_value * 100) / 100).toFixed(2)}<\/td>
            <td class="px-6 py-4 text-sm"><span class="px-2 py-1 text-xs rounded ${statusClass}">${statusLabel}<\/span><\/td>
            <td class="px-6 py-4 text-sm space-x-2">
                <button onclick="viewOrder(${order.id})" class="text-blue-600 hover:text-blue-800">Ver</button>
                <button onclick="downloadPdf(${order.id})" class="text-green-600 hover:text-green-800">PDF</button>
                ${order.status === 'draft' ? `<button onclick="confirmOrder(${order.id})" class="text-purple-600 hover:text-purple-800">Confirmar</button>` : ''}
                <button onclick="deleteOrder(${order.id})" class="text-red-600 hover:text-red-800">Eliminar</button>
            <\/td>
        </td>`;
    });
    tbody.innerHTML = html;
}

function getStatusClass(status) {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        confirmed: 'bg-blue-100 text-blue-800',
        processing: 'bg-yellow-100 text-yellow-800',
        shipped: 'bg-purple-100 text-purple-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getStatusLabel(status) {
    const labels = {
        draft: 'Rascunho',
        confirmed: 'Confirmada',
        processing: 'Processamento',
        shipped: 'Enviada',
        delivered: 'Entregue',
        cancelled: 'Cancelada'
    };
    return labels[status] || status;
}

function renderPagination(data) {
    const pagination = document.getElementById('pagination');
    if (!data || data.last_page <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '<div class="flex justify-between items-center">';
    html += `<div class="text-sm text-gray-500">Mostrando ${data.from || 0} a ${data.to || 0} de ${data.total}<\/div>`;
    html += '<div class="flex space-x-1">';
    
    if (data.current_page > 1) {
        html += `<button onclick="goToPage(${data.current_page - 1})" class="px-3 py-1 border rounded hover:bg-gray-50">Anterior<\/button>`;
    }
    
    for (let i = 1; i <= data.last_page; i++) {
        if (i === data.current_page) {
            html += `<button class="px-3 py-1 bg-indigo-600 text-white rounded">${i}<\/button>`;
        } else if (Math.abs(i - data.current_page) <= 2) {
            html += `<button onclick="goToPage(${i})" class="px-3 py-1 border rounded hover:bg-gray-50">${i}<\/button>`;
        }
    }
    
    if (data.current_page < data.last_page) {
        html += `<button onclick="goToPage(${data.current_page + 1})" class="px-3 py-1 border rounded hover:bg-gray-50">Próximo<\/button>`;
    }
    
    html += '<\/div><\/div>';
    pagination.innerHTML = html;
}

function filterByStatus() {
    currentStatus = document.getElementById('statusFilter').value;
    currentPage = 1;
    loadOrders();
}

function goToPage(page) {
    currentPage = page;
    loadOrders();
}

function viewOrder(id) {
    window.location.href = `/orders/${id}`;
}

function downloadPdf(id) {
    window.open(`/api/orders/${id}/download-pdf`, '_blank');
}

function confirmOrder(id) {
    if (confirm('Confirmar esta encomenda? Esta ação não poderá ser desfeita.')) {
        fetch(`/api/orders/${id}/close`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Encomenda confirmada com sucesso!');
                loadOrders();
            } else {
                alert('Erro: ' + (result.message || 'Erro desconhecido'));
            }
        });
    }
}

function deleteOrder(id) {
    if (confirm('Tem certeza que deseja eliminar esta encomenda?')) {
        fetch(`/api/orders/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Encomenda eliminada com sucesso!');
                loadOrders();
            } else {
                alert('Erro: ' + (result.message || 'Erro desconhecido'));
            }
        });
    }
}

loadClients();
loadArticles();
loadOrders();
</script>
@endsection