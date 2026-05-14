@extends('layouts.cliente')

@section('title', 'Minhas Encomendas')
@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Minhas Encomendas</h1>
        <p class="text-sm text-gray-500 mt-1">Acompanhe o status das suas encomendas</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($encomendas as $encomenda)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-mono">{{ $encomenda['number'] ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm">{{ $encomenda['created_at'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm font-semibold">{{ number_format($encomenda['total_value'] ?? 0, 2) }}€</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">
                            {{ $encomenda['status'] ?? 'Rascunho' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="#" class="text-blue-600 hover:text-blue-800">Ver detalhes</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <p class="mt-2">Nenhuma encomenda encontrada</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection