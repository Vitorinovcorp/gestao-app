@extends('layouts.app')

@section('title', 'Novo Negócio')
@section('header', 'Novo Negócio')

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

    <form action="{{ route('deals.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
            <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Entidade</label>
                <select name="entity_id" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Selecione uma entidade</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity->id }}" {{ old('entity_id') == $entity->id ? 'selected' : '' }}>
                            {{ $entity->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pessoa de contacto</label>
                <select name="person_id" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Selecione uma pessoa</option>
                    @foreach($people as $person)
                        <option value="{{ $person->id }}" {{ old('person_id') == $person->id ? 'selected' : '' }}>
                            {{ $person->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
                <input type="number" step="0.01" name="value" value="{{ old('value') }}" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Probabilidade (%)</label>
                <input type="number" min="0" max="100" name="probability" value="{{ old('probability', 0) }}" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Etapa</label>
                <select name="stage" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="lead" {{ old('stage') == 'lead' ? 'selected' : '' }}>Lead</option>
                    <option value="proposal" {{ old('stage') == 'proposal' ? 'selected' : '' }}>Proposta</option>
                    <option value="negotiation" {{ old('stage') == 'negotiation' ? 'selected' : '' }}>Negociação</option>
                    <option value="follow_up" {{ old('stage') == 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                    <option value="won" {{ old('stage') == 'won' ? 'selected' : '' }}>Ganho</option>
                    <option value="lost" {{ old('stage') == 'lost' ? 'selected' : '' }}>Perdido</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data prevista</label>
                <input type="date" name="expected_close_date" value="{{ old('expected_close_date') }}" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
            <select name="owner_id" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('owner_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('deals.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Criar Negócio
            </button>
        </div>
    </form>
</div>
@endsection