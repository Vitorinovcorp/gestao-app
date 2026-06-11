@extends('layouts.app')

@section('title', 'Estatísticas de Produtos')
@section('header', 'Estatísticas de Produtos')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">📊 Estatísticas de Produtos</h2>
        <div class="flex gap-2">
            <a href="{{ route('deals.statistics.export', request()->all()) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                <i class="fa-solid fa-file-export"></i> Exportar CSV
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" class="mb-6 bg-gray-50 p-4 rounded-lg flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700">Estado</label>
            <select name="stage" class="border rounded px-3 py-2">
                <option value="">Todos</option>
                @foreach($stages as $stage)
                <option value="{{ $stage }}" {{ request('stage') == $stage ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $stage)) }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Responsável</label>
            <select name="owner_id" class="border rounded px-3 py-2">
                <option value="">Todos</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('owner_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Data Início</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Data Fim</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-3 py-2">
        </div>
        <div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>
            <a href="{{ route('deals.statistics') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 ml-2">
                Limpar
            </a>
        </div>
    </form>

    <!-- Tabela -->
    @if($stats->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Referência</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                    <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase">Quantidade</th>
                    <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase">Valor Total</th>
                    <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase">% Pipeline</th>
                    <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $grandTotal = $stats->sum('total_value');
                @endphp
                @foreach($stats as $stat)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat->reference }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $stat->article_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ $stat->total_quantity }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($stat->total_value, 2) }} €</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        @php
                            $percent = $grandTotal > 0 ? ($stat->total_value / $grandTotal) * 100 : 0;
                        @endphp
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 rounded-full h-2" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="text-xs">{{ number_format($percent, 1) }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <a href="{{ route('deals.statistics.details', $stat->id) }}" class="text-indigo-600 hover:text-indigo-800">
                            <i class="fa-solid fa-eye"></i> Detalhes
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
               
            </tfoot>
        </table>
    </div>
    <div class="mt-4">
        {{ $stats->links() }}
    </div>
    @else
    <div class="text-center py-8 text-gray-500">
        <i class="fa-solid fa-chart-simple text-3xl mb-2"></i>
        <p>Nenhum produto encontrado nos negócios.</p>
        <p class="text-sm">Adicione produtos aos seus negócios para ver as estatísticas.</p>
    </div>
    @endif
</div>
@endsection