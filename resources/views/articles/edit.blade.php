@extends('layouts.app')

@section('title', 'Editar Artigo')
@section('header', 'Editar Artigo')

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

    <form action="{{ route('api.articles.update', $article->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Referência *</label>
            <input type="text" name="reference" value="{{ $article->reference }}" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
            <input type="text" name="name" value="{{ $article->name }}" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ $article->description }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço (€) *</label>
                <input type="number" step="0.01" name="price" value="{{ $article->price }}" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Custo (€)</label>
                <input type="number" step="0.01" name="cost_price" value="{{ $article->cost_price }}" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">IVA</label>
                <select name="vat_id" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($vatRates as $vat)
                        <option value="{{ $vat->id }}" {{ $article->vat_id == $vat->id ? 'selected' : '' }}>
                            {{ $vat->name }} ({{ $vat->rate }}%)
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                <input type="number" name="stock_current" value="{{ $article->stock_current }}" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ $article->is_active ? 'checked' : '' }} class="mr-2">
                <span class="text-sm text-gray-700">Ativo</span>
            </label>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('articles.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Atualizar
            </button>
        </div>
    </form>
</div>
@endsection