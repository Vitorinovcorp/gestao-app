@extends('layouts.app')

@section('title', 'Encomenda ' . ($encomenda->number ?? ''))
@section('header', 'Detalhe da Encomenda')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-lg">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">ENCOMENDA</h1>
            <p class="text-gray-500 text-sm">Nº {{ $encomenda->number }}</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="window.location.href='/orders'" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Voltar
            </button>
            <button onclick="downloadPdf()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Descarregar PDF
            </button>
            @if($encomenda->status == 'rascunho')
            <button onclick="confirmOrder()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Confirmar Encomenda
            </button>
            @endif
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-500">Cliente</p>
                <p class="font-medium">{{ $encomenda->client->name ?? '-' }}</p>
                <p class="text-sm">NIF: {{ $encomenda->client->nif ?? '-' }}</p>
                <p class="text-sm">Email: {{ $encomenda->client->email ?? '-' }}</p>
                <p class="text-sm">Telefone: {{ $encomenda->client->mobile ?? $encomenda->client->phone ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <span class="px-2 py-1 text-xs rounded 
                    @if($encomenda->status == 'rascunho') bg-gray-100 text-gray-800
                    @elseif($encomenda->status == 'confirmada') bg-blue-100 text-blue-800
                    @elseif($encomenda->status == 'entregue') bg-green-100 text-green-800
                    @else bg-yellow-100 text-yellow-800 @endif">
                    @if($encomenda->status == 'rascunho') Rascunho
                    @elseif($encomenda->status == 'confirmada') Confirmada
                    @elseif($encomenda->status == 'entregue') Entregue
                    @else {{ ucfirst($encomenda->status) }}
                    @endif
                </span>
                @if($encomenda->expected_delivery)
                <p class="text-sm text-gray-500 mt-2">Entrega Prevista</p>
                <p class="text-sm">{{ \Carbon\Carbon::parse($encomenda->expected_delivery)->format('d/m/Y') }}</p>
                @endif
                @if($encomenda->order_date)
                <p class="text-sm text-gray-500 mt-2">Data da Encomenda</p>
                <p class="text-sm">{{ \Carbon\Carbon::parse($encomenda->order_date)->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>

        <h3 class="font-semibold mb-3">Itens da Encomenda</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-3 text-left text-sm font-semibold border">Descrição</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold border">Qtd</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold border">Preço Unit.</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold border">IVA</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold border">Valor IVA</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold border">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalSemIva = 0;
                        $totalIva = 0;
                    @endphp
                    @forelse($encomenda->lines as $line)
                    @php
                        $subtotal = $line->quantity * $line->unit_price;
                        $valorIva = $subtotal * ($line->vat_rate / 100);
                        $totalSemIva += $subtotal;
                        $totalIva += $valorIva;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm border">{{ $line->article->name ?? 'Produto' }}</td>
                        <td class="px-4 py-3 text-sm text-center border">{{ $line->quantity }}</td>
                        <td class="px-4 py-3 text-sm text-right border">€ {{ number_format($line->unit_price, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right border">{{ number_format($line->vat_rate, 0) }}%</td>
                        <td class="px-4 py-3 text-sm text-right border">€ {{ number_format($valorIva, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right border">€ {{ number_format($subtotal, 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500 border">Nenhum item encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="px-4 py-3 text-right font-bold border">Subtotal:</td>
                        <td colspan="2" class="px-4 py-3 text-right font-bold border">€ {{ number_format($totalSemIva, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="px-4 py-3 text-right font-bold border">IVA (Total):</td>
                        <td colspan="2" class="px-4 py-3 text-right font-bold border">€ {{ number_format($totalIva, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-gray-100">
                        <td colspan="4" class="px-4 py-3 text-right font-bold text-blue-600 border">TOTAL FINAL:</td>
                        <td colspan="2" class="px-4 py-3 text-right font-bold text-blue-600 border">€ {{ number_format($totalSemIva + $totalIva, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($encomenda->notes)
        <div class="mt-4 p-3 bg-gray-50 rounded">
            <p class="text-sm text-gray-500">Observações:</p>
            <p class="text-sm">{{ $encomenda->notes }}</p>
        </div>
        @endif
    </div>
</div>

<script>
const orderId = {{ $encomenda->id }};

function downloadPdf() {
    window.open(`/api/orders/${orderId}/download-pdf`, '_blank');
}

function confirmOrder() {
    if (confirm('Confirmar esta encomenda?')) {
        fetch(`/api/orders/${orderId}/close`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(response => response.json())
        .then(() => window.location.reload());
    }
}
</script>
@endsection