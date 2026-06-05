@extends('layouts.app')

@section('title', 'Novo Formulário Público')
@section('header', 'Novo Formulário Público')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('public-forms.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Título do Formulário *</label>
            <input type="text" name="title" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem de Confirmação</label>
            <textarea name="confirmation_message" rows="3" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">Obrigado! O seu formulário foi submetido com sucesso.</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">URL de Sucesso (opcional)</label>
            <input type="url" name="success_url" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            <p class="text-xs text-gray-500">Se preenchido, o utilizador será redirecionado para esta URL após submissão.</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Campos do Formulário</label>
            <div id="fieldsContainer" class="space-y-2">
                <div class="field-item grid grid-cols-12 gap-2">
                    <input type="hidden" name="fields[0][id]" value="0">
                    <div class="col-span-4">
                        <input type="text" name="fields[0][label]" placeholder="Nome do campo" required class="w-full px-2 py-1 border rounded text-sm">
                    </div>
                    <div class="col-span-3">
                        <select name="fields[0][type]" class="w-full px-2 py-1 border rounded text-sm">
                            <option value="text">Texto</option>
                            <option value="email">Email</option>
                            <option value="phone">Telefone</option>
                            <option value="textarea">Área de Texto</option>
                            <option value="select">Seleção</option>
                        </select>
                    </div>
                    <div class="col-span-2 flex items-center">
                        <input type="checkbox" name="fields[0][required]" value="1" class="mr-1">
                        <span class="text-sm text-gray-600">Obrigatório</span>
                    </div>
                    <div class="col-span-2">
                        <button type="button" onclick="removeField(this)" class="text-red-500 hover:text-red-700">✗</button>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addField()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                + Adicionar Campo
            </button>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('public-forms.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Criar Formulário
            </button>
        </div>
    </form>
</div>

<script>
let fieldCount = 1;

function addField() {
    const container = document.getElementById('fieldsContainer');
    const fieldId = Date.now() + '_' + fieldCount;
    const newField = document.createElement('div');
    newField.className = 'field-item grid grid-cols-12 gap-2';
    newField.innerHTML = `
        <input type="hidden" name="fields[${fieldId}][id]" value="${fieldId}">
        <div class="col-span-4">
            <input type="text" name="fields[${fieldId}][label]" placeholder="Nome do campo" required class="w-full px-2 py-1 border rounded text-sm">
        </div>
        <div class="col-span-3">
            <select name="fields[${fieldId}][type]" class="w-full px-2 py-1 border rounded text-sm">
                <option value="text">Texto</option>
                <option value="email">Email</option>
                <option value="phone">Telefone</option>
                <option value="textarea">Área de Texto</option>
                <option value="select">Seleção</option>
            </select>
        </div>
        <div class="col-span-2 flex items-center">
            <input type="checkbox" name="fields[${fieldId}][required]" value="1" class="mr-1">
            <span class="text-sm text-gray-600">Obrigatório</span>
        </div>
        <div class="col-span-2">
            <button type="button" onclick="removeField(this)" class="text-red-500 hover:text-red-700">✗</button>
        </div>
    `;
    container.appendChild(newField);
    fieldCount++;
}

function removeField(button) {
    const container = document.getElementById('fieldsContainer');
    if (container.children.length > 1) {
        button.closest('.field-item').remove();
    } else {
        alert('É necessário pelo menos um campo.');
    }
}
</script>
@endsection