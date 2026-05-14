@extends('layouts.app')

@section('title', 'Configurações')
@section('header', 'Configurações')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Configurações da Empresa -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Configurações da Empresa</h2>
        </div>
        <div class="p-4">
            <form id="companyForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Empresa</label>
                    <input type="text" name="name" id="companyName" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="companyEmail" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="phone" id="companyPhone" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg">Salvar</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Configurações Gerais</h2>
        </div>
        <div class="p-4">
            <form id="generalForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Aplicação</label>
                    <input type="text" name="app_name" id="appName" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                    <select name="timezone" id="timezone" class="w-full px-3 py-2 border rounded-lg">
                        <option value="Europe/Lisbon">Europe/Lisbon</option>
                        <option value="Europe/London">Europe/London</option>
                        <option value="America/New_York">America/New_York</option>
                    </select>
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg">Salvar</button>
            </form>
        </div>
    </div>
</div>

<script>
    function loadCompanySettings() {
        fetch('/api/settings/company')
            .then(response => response.json())
            .then(data => {
                document.getElementById('companyName').value = data.name || '';
                document.getElementById('companyEmail').value = data.email || '';
                document.getElementById('companyPhone').value = data.phone || '';
            });
    }

    function loadGeneralSettings() {
        fetch('/api/settings/general')
            .then(response => response.json())
            .then(data => {
                document.getElementById('appName').value = data.app_name || '';
                document.getElementById('timezone').value = data.timezone || 'Europe/Lisbon';
            });
    }

    document.getElementById('companyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('/api/settings/company', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                name: document.getElementById('companyName').value,
                email: document.getElementById('companyEmail').value,
                phone: document.getElementById('companyPhone').value
            })
        }).then(() => alert('Configurações salvas!'));
    });

    document.getElementById('generalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('/api/settings/general', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                app_name: document.getElementById('appName').value,
                timezone: document.getElementById('timezone').value
            })
        }).then(() => alert('Configurações salvas!'));
    });

    loadCompanySettings();
    loadGeneralSettings();
</script>
@endsection