@extends('layouts.app')

@section('title', 'Encomenda a Fornecedor')
@section('header', 'Detalhe da Encomenda')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-lg">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">ENCOMENDA A FORNECEDOR</h1>
            <p class="text-gray-500 text-sm">Nº {{ $encomenda->number }}</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="window.location.href='/supplier-orders'" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Voltar
            </button>
            <button onclick="downloadPdf()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Descarregar PDF
            </button>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-500">Fornecedor</p>
                <p class="font-medium">{{ $encomenda->supplier->name ?? '-' }}</p>
                <p class="text-sm">NIF: {{ $encomenda->supplier->nif ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">
                    {{ ucfirst($encomenda->status) }}
                </span>
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
                        <th class="px-4 py-3 text-right text-sm font-semibold border">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @forelse($encomenda->lines as $line)
                    @php $subtotal = $line->quantity * $line->unit_price; $total += $subtotal; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm border">{{ $line->article->name ?? 'Produto' }}</td>
                        <td class="px-4 py-3 text-sm text-center border">{{ $line->quantity }}</td>
                        <td class="px-4 py-3 text-sm text-right border">€ {{ number_format($line->unit_price, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right border">€ {{ number_format($subtotal, 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-500 border">Nenhum item encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100">
                        <td colspan="3" class="px-4 py-3 text-right font-bold border">TOTAL:</td>
                        <td class="px-4 py-3 text-right font-bold text-blue-600 border">€ {{ number_format($total, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
const orderId = {{ $encomenda->id }};

function downloadPdf() {
    window.open(`/api/supplier-orders/${orderId}/download-pdf`, '_blank');
}
</script>
@endsection