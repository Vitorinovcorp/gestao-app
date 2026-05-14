@extends('layouts.cliente')

@section('title', 'Minhas Propostas')
@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Minhas Propostas</h1>
        <p class="text-sm text-gray-500 mt-1">Acompanhe o status das suas propostas</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Validade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($propostas as $proposta)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-mono">{{ $proposta['number'] ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm">{{ $proposta['created_at'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm">{{ $proposta['validity'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm font-semibold">{{ number_format($proposta['total_value'] ?? 0, 2) }}€</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">
                            {{ $proposta['status'] ?? 'Rascunho' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="#" class="text-blue-600 hover:text-blue-800">Ver detalhes</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="mt-2">Nenhuma proposta encontrada</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection