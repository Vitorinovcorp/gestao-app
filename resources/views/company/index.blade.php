@extends('layouts.app')

@section('title', 'Configurações da Empresa')
@section('header', 'Configurações da Empresa')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-2">Dados da Empresa</h2>
        <p class="text-gray-600">Configure os dados que aparecem nos PDFs e na aplicação</p>
    </div>

    <form id="companyForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Logo -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Logotipo da Empresa</label>
            <div class="flex items-center space-x-4">
                <div id="logoPreview" class="w-32 h-32 border-2 border-gray-300 rounded-lg overflow-hidden bg-gray-50 flex items-center justify-center">
                    @if($settings->logo_path)
                        <img src="{{ Storage::url($settings->logo_path) }}" alt="Logo" class="w-full h-full object-contain">
                    @else
                        <i class="fa-solid fa-building text-4xl text-gray-400"></i>
                    @endif
                </div>
                <div>
                    <input type="file" id="logoInput" accept="image/*" class="hidden">
                    <button type="button" id="uploadLogoBtn" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        <i class="fa-solid fa-upload mr-2"></i>Carregar Logo
                    </button>
                    <button type="button" id="removeLogoBtn" class="ml-2 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 {{ !$settings->logo_path ? 'hidden' : '' }}">
                        <i class="fa-solid fa-trash mr-2"></i>Remover
                    </button>
                    <p class="text-xs text-gray-500 mt-2">Formatos: JPG, PNG, GIF. Máx: 2MB</p>
                </div>
            </div>
        </div>

        <!-- Nome da Empresa -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Empresa *</label>
            <input type="text" name="name" value="{{ $settings->name }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <!-- Morada -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Morada</label>
            <input type="text" name="address" value="{{ $settings->address }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <!-- Código Postal -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Código Postal</label>
                <input type="text" name="postal_code" value="{{ $settings->postal_code }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                    placeholder="1234-567">
            </div>

            <!-- Localidade -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Localidade</label>
                <input type="text" name="city" value="{{ $settings->city }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
        </div>

        <!-- Número Contribuinte -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Número de Contribuinte (NIF)</label>
            <input type="text" name="tax_number" value="{{ $settings->tax_number }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                placeholder="123456789">
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ $settings->email }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- Telefone -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                <input type="text" name="phone" value="{{ $settings->phone }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
        </div>

        <!-- Website -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
            <input type="url" name="website" value="{{ $settings->website }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                placeholder="https://www.exemplo.com">
        </div>

        <!-- Botões -->
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="resetForm()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancelar
            </button>
            <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">
                <i class="fa-solid fa-save mr-2"></i>Guardar Configurações
            </button>
        </div>
    </form>
</div>

<script>
    const uploadBtn = document.getElementById('uploadLogoBtn');
    const logoInput = document.getElementById('logoInput');
    const removeBtn = document.getElementById('removeLogoBtn');
    const logoPreview = document.getElementById('logoPreview');

    uploadBtn.addEventListener('click', () => logoInput.click());

    logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('logo', file);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("company.upload-logo") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    logoPreview.innerHTML = `<img src="${data.path}?t=${Date.now()}" alt="Logo" class="w-full h-full object-contain">`;
                    removeBtn.classList.remove('hidden');
                    showNotification('Logotipo atualizado!', 'success');
                }
            });
        }
    });

    removeBtn.addEventListener('click', function() {
        if (confirm('Tem certeza que deseja remover o logotipo?')) {
            fetch('{{ route("company.delete-logo") }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    logoPreview.innerHTML = '<i class="fa-solid fa-building text-4xl text-gray-400"></i>';
                    removeBtn.classList.add('hidden');
                    showNotification('Logotipo removido!', 'success');
                }
            });
        }
    });

    document.getElementById('companyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: document.getElementById('name').value,
            address: document.getElementById('address').value,
            postal_code: document.getElementById('postal_code').value,
            city: document.getElementById('city').value,
            tax_number: document.getElementById('tax_number').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            website: document.getElementById('website').value,
            _token: '{{ csrf_token() }}'
        };
        
        fetch('{{ route("company.update") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Configurações guardadas com sucesso!', 'success');
                // Limpar o formulário após salvar com sucesso
                setTimeout(() => {
                    resetForm();
                }, 1500);
            } else {
                showNotification('Erro ao guardar configurações', 'error');
            }
        })
        .catch(error => {
            showNotification('Erro ao guardar configurações', 'error');
        });
    });

    function resetForm() {
        // Limpar todos os campos do formulário
        document.getElementById('name').value = '';
        document.getElementById('address').value = '';
        document.getElementById('postal_code').value = '';
        document.getElementById('city').value = '';
        document.getElementById('tax_number').value = '';
        document.getElementById('email').value = '';
        document.getElementById('phone').value = '';
        document.getElementById('website').value = '';
        
        // Opcional: Focar no primeiro campo
        document.getElementById('name').focus();
        
        showNotification('Formulário limpo! Pode cadastrar outra empresa.', 'info');
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            'bg-blue-500'
        } text-white`;
        notification.innerHTML = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
</script>
@endsection