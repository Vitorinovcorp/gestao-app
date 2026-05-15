@extends('layouts.app')

@section('title', 'Faturas de Fornecedor')
@section('header', 'Faturas de Fornecedor')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex space-x-2">
            <select id="statusFilter" onchange="filterByStatus()" class="px-3 py-1 border rounded text-sm">
                <option value="">Todos os status</option>
                <option value="pending">Pendente</option>
                <option value="paid">Paga</option>
            </select>
        </div>
        <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            + Nova Fatura
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fornecedor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Encomenda</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Documento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody id="invoicesTable">
                <tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">Carregando...<\/td></tr>
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t border-gray-200" id="pagination"></div>
</div>

<!-- Modal de Criar/Editar Fatura -->
<div id="invoiceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Nova Fatura</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        
        <form id="invoiceForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="invoiceId">
            <input type="hidden" id="method" value="POST">
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número da Fatura *</label>
                    <input type="text" id="number" required class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor *</label>
                    <select id="supplier_id" required class="w-full px-3 py-2 border rounded-md">
                        <option value="">Selecione</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data da Fatura *</label>
                    <input type="date" id="invoice_date" required class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Vencimento *</label>
                    <input type="date" id="due_date" required class="w-full px-3 py-2 border rounded-md">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Encomenda Fornecedor</label>
                    <select id="supplier_order_id" class="w-full px-3 py-2 border rounded-md">
                        <option value="">Selecione</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total (€) *</label>
                    <input type="number" step="0.01" id="total_value" required class="w-full px-3 py-2 border rounded-md">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Documento</label>
                <input type="file" id="document" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-3 py-2 border rounded-md">
                <p class="text-xs text-gray-500 mt-1">PDF, JPG ou PNG (max 5MB)</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea id="observations" rows="3" class="w-full px-3 py-2 border rounded-md"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Pagamento -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Marcar Fatura como Paga</h3>
            <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        
        <form id="paymentForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="paymentInvoiceId">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Comprovativo de Pagamento</label>
                <input type="file" id="payment_proof" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-3 py-2 border rounded-md">
                <p class="text-xs text-gray-500 mt-1">PDF, JPG ou PNG (max 5MB)</p>
            </div>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" id="send_email" class="mr-2" checked>
                    <span class="text-sm text-gray-700">Enviar comprovativo por email ao fornecedor</span>
                </label>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Confirmar Pagamento</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPage = 1;
let currentStatus = '';
let currentInvoiceId = null;

function loadSuppliers() {
    fetch('/api/entities/suppliers')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('supplier_id');
            const suppliers = data.data || [];
            select.innerHTML = '<option value="">Selecione um fornecedor</option>';
            suppliers.forEach(supplier => {
                select.innerHTML += `<option value="${supplier.id}">${supplier.name} (${supplier.nif})</option>`;
            });
        });
}

function loadSupplierOrders() {
    const supplierId = document.getElementById('supplier_id').value;
    if (!supplierId) {
        console.log('Nenhum fornecedor selecionado');
        return;
    }
    
    console.log('Buscando encomendas para fornecedor:', supplierId);
    
    fetch(`/api/supplier-orders?supplier_id=${supplierId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Encomendas recebidas:', data);
            const select = document.getElementById('supplier_order_id');
            const orders = data.data || [];
            select.innerHTML = '<option value="">Selecione uma encomenda</option>';
            
            if (orders.length === 0) {
                select.innerHTML += '<option disabled>Nenhuma encomenda encontrada</option>';
            }
            
            orders.forEach(order => {
                select.innerHTML += `<option value="${order.id}">${order.number} - €${order.total_value} (${order.status})</option>`;
            });
        })
        .catch(error => console.error('Erro ao carregar encomendas:', error));
}

function loadInvoices() {
    let url = `/api/supplier-invoices?page=${currentPage}`;
    if (currentStatus) url += `&status=${currentStatus}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            renderTable(data.data);
            renderPagination(data);
        });
}

function renderTable(invoices) {
    const tbody = document.getElementById('invoicesTable');
    if (!invoices || invoices.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">Nenhuma fatura encontrada<\/td></tr>';
        return;
    }
    
    let html = '';
    invoices.forEach(invoice => {
        const statusClass = invoice.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
        const statusLabel = invoice.status === 'paid' ? 'Paga' : 'Pendente';
        
        html += `<tr class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm">${invoice.invoice_date ? invoice.invoice_date.split('T')[0] : '-'}<\/td>
            <td class="px-6 py-4 text-sm font-mono">${invoice.number}<\/td>
            <td class="px-6 py-4 text-sm">${invoice.supplier?.name || '-'}<\/td>
            <td class="px-6 py-4 text-sm">${invoice.supplier_order?.number || '-'}<\/td>
            <td class="px-6 py-4 text-sm">
                ${invoice.document_path ? `<a href="/api/supplier-invoices/${invoice.id}/download-document" target="_blank" class="text-blue-600 hover:underline">📄 Download</a>` : '-'}
            <\/td>
            <td class="px-6 py-4 text-sm font-semibold">€ ${parseFloat(invoice.total_value).toFixed(2)}<\/td>
            <td class="px-6 py-4 text-sm"><span class="px-2 py-1 text-xs rounded ${statusClass}">${statusLabel}<\/span><\/td>
            <td class="px-6 py-4 text-sm space-x-2">
                <button onclick="editInvoice(${invoice.id})" class="text-blue-600 hover:text-blue-800">Editar</button>
                ${invoice.status === 'pending' ? `<button onclick="openPaymentModal(${invoice.id})" class="text-green-600 hover:text-green-800">Pagar</button>` : ''}
                <button onclick="deleteInvoice(${invoice.id})" class="text-red-600 hover:text-red-800">Eliminar</button>
            <\/td>
        </td>`;
    });
    tbody.innerHTML = html;
}

function renderPagination(data) {
    const pagination = document.getElementById('pagination');
    if (!data || data.last_page <= 1) return;
    
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
    loadInvoices();
}

function goToPage(page) {
    currentPage = page;
    loadInvoices();
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Nova Fatura';
    document.getElementById('invoiceForm').reset();
    document.getElementById('invoiceId').value = '';
    document.getElementById('method').value = 'POST';
    document.getElementById('invoiceModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('invoiceModal').classList.add('hidden');
}

function editInvoice(id) {
    fetch(`/api/supplier-invoices/${id}`)
        .then(response => response.json())
        .then(invoice => {
            document.getElementById('modalTitle').textContent = 'Editar Fatura';
            document.getElementById('invoiceId').value = invoice.id;
            document.getElementById('method').value = 'PUT';
            document.getElementById('number').value = invoice.number;
            document.getElementById('supplier_id').value = invoice.supplier_id;
            document.getElementById('invoice_date').value = invoice.invoice_date;
            document.getElementById('due_date').value = invoice.due_date;
            document.getElementById('supplier_order_id').value = invoice.supplier_order_id || '';
            document.getElementById('total_value').value = invoice.total_value;
            document.getElementById('observations').value = invoice.observations || '';
            document.getElementById('invoiceModal').classList.remove('hidden');
        });
}

function deleteInvoice(id) {
    if (confirm('Tem certeza que deseja eliminar esta fatura?')) {
        fetch(`/api/supplier-invoices/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        }).then(() => loadInvoices());
    }
}

function openPaymentModal(id) {
    currentInvoiceId = id;
    document.getElementById('paymentForm').reset();
    document.getElementById('paymentModal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    currentInvoiceId = null;
}

function saveInvoice(event) {
    event.preventDefault();
    
    const id = document.getElementById('invoiceId').value;
    const method = document.getElementById('method').value;
    
    const formData = new FormData();
    formData.append('number', document.getElementById('number').value);
    formData.append('supplier_id', document.getElementById('supplier_id').value);
    formData.append('invoice_date', document.getElementById('invoice_date').value);
    formData.append('due_date', document.getElementById('due_date').value);
    formData.append('supplier_order_id', document.getElementById('supplier_order_id').value);
    formData.append('total_value', document.getElementById('total_value').value);
    formData.append('observations', document.getElementById('observations').value);
    
    const documentFile = document.getElementById('document').files[0];
    if (documentFile) formData.append('document', documentFile);
    
    let url = '/api/supplier-invoices';
    if (id) {
        formData.append('_method', 'PUT');
        url = `/api/supplier-invoices/${id}`;
    }
    
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            closeModal();
            loadInvoices();
            alert('Fatura salva com sucesso!');
        } else {
            alert('Erro: ' + result.message);
        }
    });
}

function processPayment(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('send_email', document.getElementById('send_email').checked ? '1' : '0');
    
    const paymentProof = document.getElementById('payment_proof').files[0];
    if (paymentProof) formData.append('payment_proof', paymentProof);
    
    fetch(`/api/supplier-invoices/${currentInvoiceId}/mark-as-paid`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            closePaymentModal();
            loadInvoices();
            alert('Pagamento registado!' + (result.email_sent ? ' Email enviado.' : ''));
        } else {
            alert('Erro: ' + result.message);
        }
    });
}

document.getElementById('invoiceForm').addEventListener('submit', saveInvoice);
document.getElementById('paymentForm').addEventListener('submit', processPayment);
document.getElementById('supplier_id').addEventListener('change', loadSupplierOrders);

loadSuppliers();
loadInvoices();
</script>
@endsection