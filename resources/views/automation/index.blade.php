@extends('layouts.app')

@section('title', 'Automatizações')
@section('header', 'Automatizações')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Regras de Automatização</h2>
        <a href="{{ route('automation.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            <i class="fa-solid fa-plus"></i> Nova Regra
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gatilho</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($rules as $rule)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $rule->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($rule->trigger_type == 'inactivity_days' || $rule->trigger_type == 'days_until_close')
                                Inatividade ({{ $rule->conditions['days'] ?? '-' }} dias)
                            @elseif($rule->trigger_type == 'value_threshold')
                                Valor > {{ $rule->conditions['value_threshold'] ?? '-' }}
                            @elseif($rule->trigger_type == 'stage_change')
                                {{ $rule->from_label ?? '-' }} → {{ $rule->to_label ?? '-' }}
                            @else
                                {{ ucfirst(str_replace('_', ' ', $rule->trigger_type)) }}
                            @endif
                            @if(isset($rule->conditions['stage']) && $rule->conditions['stage'])
                                - {{ ucfirst(str_replace('_', ' ', $rule->conditions['stage'])) }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $rule->priority_label }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $rule->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $rule->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="{{ route('automation.edit', $rule->id) }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('automation.toggle-status', $rule->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-yellow-600 hover:text-yellow-800">
                                    <i class="fa-solid fa-power-off"></i>
                                </button>
                            </form>
                            <form action="{{ route('automation.destroy', $rule->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja eliminar esta regra?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection