@extends('layouts.cliente')

@section('title', 'Proposta ' . ($proposta->number ?? ''))
@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">Proposta {{ $proposta->number ?? '' }}</h1>
            <p class="text-sm text-gray-500">Emitida em {{ $proposta->created_at->format('d/m/Y') }}</p>
        </div>
        <div>
            <a href="{{ route('cliente.propostas') }}" class="text-gray-600 hover:text-gray-800">← Voltar</a>
        </div>
    </div>

    <div class="p-6">
        <!-- Status -->
        <div class="mb-6">
            <span class="px-3 py-1 text-sm rounded 
                @if($proposta->status == 'closed') bg-green-100 text-green-800
                @elseif($proposta->status == 'sent') bg-blue-100 text-blue-800
                @else bg-gray-100 text-gray-800 @endif">
                Status: {{ $proposta->status_label ?? $proposta->status }}
            </span>
        </div>

        <!-- Informações -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-500">Data de Validade</p>
                <p class="font-medium">{{ $proposta->validity?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Valor Total</p>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($proposta->total_value, 2) }}€</p>
            </div>
        </div>

        <!-- Linhas da proposta -->
        <div class="mt-6">
            <h3 class="font-semibold text-gray-800 mb-3">Itens da Proposta</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Produto</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Qtd</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Preço Unit.</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($proposta->lines as $line)
                        <tr>
                            <td class="px-4 py-2 text-sm">{{ $line->article->name }}</td>
                            <td class="px-4 py-2 text-sm">{{ $line->quantity }}</td>
                            <td class="px-4 py-2 text-sm text-right">{{ number_format($line->unit_price, 2) }}€</td>
                            <td class="px-4 py-2 text-sm text-right font-semibold">{{ number_format($line->quantity * $line->unit_price, 2) }}€</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right font-semibold">Total:</td>
                            <td class="px-4 py-2 text-right font-bold text-blue-600">{{ number_format($proposta->total_value, 2) }}€</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Observações -->
        @if($proposta->notes)
        <div class="mt-6 p-4 bg-gray-50 rounded">
            <p class="text-sm text-gray-500">Observações:</p>
            <p class="text-sm">{{ $proposta->notes }}</p>
        </div>
        @endif

        <!-- Botão para download do PDF -->
        <div class="mt-6">
            <a href="{{ route('cliente.propostas.download', $proposta->id) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Descarregar PDF
            </a>
        </div>
    </div>
</div>
@endsection