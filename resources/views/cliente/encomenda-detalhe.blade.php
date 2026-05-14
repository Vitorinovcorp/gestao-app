@extends('layouts.cliente')

@section('title', 'Encomenda ' . ($encomenda->number ?? ''))
@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">Encomenda {{ $encomenda->number ?? '' }}</h1>
            <p class="text-sm text-gray-500">Emitida em {{ $encomenda->created_at->format('d/m/Y') }}</p>
        </div>
        <div>
            <a href="{{ route('cliente.encomendas') }}" class="text-gray-600 hover:text-gray-800">← Voltar</a>
        </div>
    </div>

    <div class="p-6">
        <!-- Status -->
        <div class="mb-6">
            <span class="px-3 py-1 text-sm rounded 
                @if($encomenda->status == 'delivered') bg-green-100 text-green-800
                @elseif($encomenda->status == 'shipped') bg-blue-100 text-blue-800
                @elseif($encomenda->status == 'processing') bg-yellow-100 text-yellow-800
                @elseif($encomenda->status == 'cancelled') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800 @endif">
                Status: {{ $encomenda->status_label ?? $encomenda->status }}
            </span>
        </div>

        <!-- Valor Total -->
        <div class="mb-6">
            <p class="text-sm text-gray-500">Valor Total</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($encomenda->total_value, 2) }}€</p>
        </div>

        <!-- Data de entrega prevista -->
        @if($encomenda->expected_delivery)
        <div class="mb-6">
            <p class="text-sm text-gray-500">Entrega Prevista</p>
            <p class="font-medium">{{ $encomenda->expected_delivery->format('d/m/Y') }}</p>
        </div>
        @endif

        <!-- Itens da encomenda -->
        <div class="mt-6">
            <h3 class="font-semibold text-gray-800 mb-3">Itens da Encomenda</h3>
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
                        @foreach($encomenda->lines as $line)
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
                            <td class="px-4 py-2 text-right font-bold text-blue-600">{{ number_format($encomenda->total_value, 2) }}€</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Observações -->
        @if($encomenda->notes)
        <div class="mt-6 p-4 bg-gray-50 rounded">
            <p class="text-sm text-gray-500">Observações:</p>
            <p class="text-sm">{{ $encomenda->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection