@extends('layouts.app')

@section('title', 'Editar Regra de Automatização')
@section('header', 'Editar Regra de Automatização')

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

    <form action="{{ route('automation.update', $rule->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Regra *</label>
            <input type="text" name="name" value="{{ $rule->name }}" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Gatilho *</label>
            <select name="trigger_type" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="inactivity_days" {{ $rule->trigger_type == 'inactivity_days' ? 'selected' : '' }}>Inatividade (dias)</option>
                <option value="stage_change" {{ $rule->trigger_type == 'stage_change' ? 'selected' : '' }}>Mudança de Estado</option>
                <option value="value_threshold" {{ $rule->trigger_type == 'value_threshold' ? 'selected' : '' }}>Limite de Valor</option>
                <option value="days_until_close" {{ $rule->trigger_type == 'days_until_close' ? 'selected' : '' }}>Dias até Fecho</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Dias de Inatividade *</label>
            <input type="number" name="days" value="{{ $rule->conditions['days'] }}" required min="1" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado (opcional)</label>
            <select name="stage" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Todos</option>
                <option value="lead" {{ ($rule->conditions['stage'] ?? '') == 'lead' ? 'selected' : '' }}>Lead</option>
                <option value="proposal" {{ ($rule->conditions['stage'] ?? '') == 'proposal' ? 'selected' : '' }}>Proposta</option>
                <option value="negotiation" {{ ($rule->conditions['stage'] ?? '') == 'negotiation' ? 'selected' : '' }}>Negociação</option>
                <option value="follow_up" {{ ($rule->conditions['stage'] ?? '') == 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                <option value="won" {{ ($rule->conditions['stage'] ?? '') == 'won' ? 'selected' : '' }}>Ganho</option>
                <option value="lost" {{ ($rule->conditions['stage'] ?? '') == 'lost' ? 'selected' : '' }}>Perdido</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Atividade *</label>
            <select name="activity_type" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="call" {{ $rule->action_config['activity_type'] == 'call' ? 'selected' : '' }}>Chamada</option>
                <option value="task" {{ $rule->action_config['activity_type'] == 'task' ? 'selected' : '' }}>Tarefa</option>
                <option value="meeting" {{ $rule->action_config['activity_type'] == 'meeting' ? 'selected' : '' }}>Reunião</option>
                <option value="note" {{ $rule->action_config['activity_type'] == 'note' ? 'selected' : '' }}>Nota</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
            <select name="priority" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="low" {{ $rule->action_config['priority'] == 'low' ? 'selected' : '' }}>Baixa</option>
                <option value="medium" {{ $rule->action_config['priority'] == 'medium' ? 'selected' : '' }}>Média</option>
                <option value="high" {{ $rule->action_config['priority'] == 'high' ? 'selected' : '' }}>Alta</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Dias para Atividade</label>
            <input type="number" name="days_offset" value="{{ $rule->action_config['days_offset'] ?? 0 }}" min="0" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ $rule->action_config['description'] ?? '' }}</textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('automation.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Atualizar Regra
            </button>
        </div>
    </form>
</div>
@endsection