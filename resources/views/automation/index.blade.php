@extends('layouts.app')

@section('title', 'Automatizações')
@section('header', 'Automatizações')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold"> Regras de Automatização</h2>
        <a href="{{ route('automation.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            <i class="fa-solid fa-plus"></i> Nova Regra
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($rules->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500">Nome</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500">Descrição</th>
                    <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500">Dias Inatividade</th>
                    <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500">Ação</th>
                    <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500">Status</th>
                    <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($rules as $rule)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $rule->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($rule->description, 50) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 text-center">{{ $rule->inactivity_days }} dias</td>
                    <td class="px-6 py-4 text-sm text-gray-500 text-center">
                        <span class="px-2 py-1 text-xs rounded {{ $rule->priority_color }}">
                            {{ ucfirst($rule->activity_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($rule->is_active)
                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Ativo</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">Pausado</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('automation.edit', $rule) }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('automation.toggle-status', $rule) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-yellow-600 hover:text-yellow-800">
                                    @if($rule->is_active)
                                        <i class="fa-solid fa-pause"></i>
                                    @else
                                        <i class="fa-solid fa-play"></i>
                                    @endif
                                </button>
                            </form>
                            <form action="{{ route('automation.destroy', $rule) }}" method="POST" class="inline" onsubmit="return confirm('Remover esta regra?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $rules->links() }}
    </div>
    @else
    <div class="text-center py-8 text-gray-500">
        <i class="fa-solid fa-robot text-3xl mb-2"></i>
        <p>Nenhuma regra de automatização criada.</p>
        <p class="text-sm">Crie regras para automatizar ações quando negócios ficarem parados.</p>
    </div>
    @endif
</div>
@endsection