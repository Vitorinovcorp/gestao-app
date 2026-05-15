@extends('layouts.app')

@section('title', 'Faturas Fornecedores')
@section('header', 'Faturas Fornecedores')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex space-x-2">
            <select id="statusFilter" onchange="filterByStatus()" class="px-3 py-1 border rounded text-sm">
                <option value="">Todos os status</option>
                <option value="rascunho">Rascunho</option>
                <option value="enviada">Enviada</option>
                <option value="confirmada">Confirmada</option>
                <option value="recebida">Recebida</option>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fornecedor</th>
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

<script>
let currentPage = 1;
let currentStatus = '';

function loadOrders() {
    let url = `/api/supplier-orders?page=${currentPage}`;
    if (currentStatus) url += `&status=${currentStatus}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            renderTable(data.data);
            renderPagination(data);
        });
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
            <td class="px-6 py-4 text-sm">${order.supplier?.name || '-'}<\/td>
            <td class="px-6 py-4 text-sm font-semibold">€ ${parseFloat(order.total_value).toFixed(2)}<\/td>
            <td class="px-6 py-4 text-sm"><span class="px-2 py-1 text-xs rounded ${statusClass}">${statusLabel}<\/span><\/td>
            <td class="px-6 py-4 text-sm space-x-2">
                <button onclick="viewOrder(${order.id})" class="text-blue-600 hover:text-blue-800">Ver</button>
                <button onclick="downloadPdf(${order.id})" class="text-green-600 hover:text-green-800">PDF</button>
            <\/td>
        <tr>`;
    });
    tbody.innerHTML = html;
}

function getStatusClass(status) {
    const classes = {
        'rascunho': 'bg-gray-100 text-gray-800',
        'enviada': 'bg-blue-100 text-blue-800',
        'confirmada': 'bg-green-100 text-green-800',
        'recebida': 'bg-purple-100 text-purple-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getStatusLabel(status) {
    const labels = {
        'rascunho': 'Rascunho',
        'enviada': 'Enviada',
        'confirmada': 'Confirmada',
        'recebida': 'Recebida'
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
    window.location.href = `/supplier-orders/${id}`;
}

function downloadPdf(id) {
    window.open(`/api/supplier-orders/${id}/download-pdf`, '_blank');
}

function openCreateModal() {
    alert('Funcionalidade em desenvolvimento');
}

loadOrders();
</script>
@endsection