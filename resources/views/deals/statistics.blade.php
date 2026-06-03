@extends('layouts.app')

@section('title', 'Estatísticas de Produtos')
@section('header', 'Estatísticas de Produtos')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Estatísticas de Produtos</h2>
        <div class="flex gap-2">
            <a href="{{ route('deals.statistics.export', request()->all()) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                <i class="fa-solid fa-file-export"></i> Exportar CSV
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" class="mb-6 bg-gray-50 p-4 rounded-lg flex flex-wrap gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Estado</label>
            <select name="stage" class="border rounded px-3 py-1">
                <option value="">Todos</option>
                @foreach(['lead', 'proposal', 'negotiation', 'follow_up', 'won', 'lost'] as $stage)
                <option value="{{ $stage }}" {{ request('stage') == $stage ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $stage)) }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Responsável</label>
            <select name="owner_id" class="border rounded px-3 py-1">
                <option value="">Todos</option>
                @foreach(\App\Models\User::all() as $user)
                <option value="{{ $user->id }}" {{ request('owner_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Data Início</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-3 py-1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Data Fim</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-3 py-1">
        </div>
        <div class="flex items-end">
            <button type="submit" class="px-4 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">Filtrar</button>
            <a href="{{ route('deals.statistics') }}" class="px-4 py-1 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 ml-2">Limpar</a>
        </div>
    </form>

    <!-- Tabela -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referência</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($stats as $stat)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat->reference }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $stat->article_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat->total_quantity }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($stat->total_value, 2) }} €</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('api.articles.edit', $stat->id) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $stats->links() }}
    </div>
</div>
@endsection