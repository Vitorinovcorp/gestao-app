@extends('layouts.app')

@section('title', 'Configurações do Tenant')
@section('header', 'Configurações do Tenant')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('tenants.update-settings') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Tenant *</label>
            <input type="text" name="name" id="name" value="{{ $tenant->name }}" 
                   class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500" 
                   required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cor Principal</label>
            <input type="color" name="primary_color" id="primary_color" value="{{ $tenant->primary_color }}" 
                   class="w-12 h-12 border rounded-md cursor-pointer">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input type="radio" name="is_active" value="1" {{ $tenant->is_active ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Ativo</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="is_active" value="0" {{ !$tenant->is_active ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Inativo</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('tenants.index') }}" 
               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Atualizar
            </button>
        </div>
    </form>
</div>
@endsection