@extends('layouts.app')

@section('title', 'Criar Novo Tenant')
@section('header', 'Criar Novo Tenant')

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

    <form action="{{ route('tenants.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Tenant *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" 
                   class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500" 
                   required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Domínio (opcional)</label>
            <input type="text" name="domain" id="domain" value="{{ old('domain') }}" 
                   class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            <p class="text-xs text-gray-500 mt-1">Ex: clientes.seudominio.com</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cor Principal</label>
            <input type="color" name="primary_color" id="primary_color" value="{{ old('primary_color', '#6D5BD0') }}" 
                   class="w-12 h-12 border rounded-md cursor-pointer">
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('tenants.index') }}" 
               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Criar Tenant
            </button>
        </div>
    </form>
</div>
@endsection