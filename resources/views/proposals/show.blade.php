@extends('layouts.app')

@section('title', 'Proposta ' . (isset($proposta) ? $proposta->number : ''))
@section('header', 'Detalhe da Proposta')

@section('content')
<div class="bg-white rounded-lg shadow">
    <!-- Cabeçalho -->
    <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-lg">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">PROPOSTA COMERCIAL</h1>
            <p class="text-gray-500 text-sm">Nº {{ $proposta->number }}</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="window.location.href='/proposals'" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                Voltar
            </button>
            <button onclick="downloadPdf()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                Descarregar PDF
            </button>
            @if($proposta->status == 'draft')
            <button onclick="closeProposal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Fechar Proposta
            </button>
            @endif
        </div>
    </div>

    <div class="p-6">
        <!-- Informações da Empresa -->
        <div class="mb-8">
            <div class="border-b pb-2 mb-4">
                <h2 class="text-lg font-semibold text-gray-700">Informações da Empresa</h2>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Nome</p>
                    <p class="font-medium">{{ $proposta->client->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">NIF</p>
                    <p class="font-medium">{{ $proposta->client->nif }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $proposta->client->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Telefone</p>
                    <p class="font-medium">{{ $proposta->client->mobile ?? $proposta->client->phone ?? '-' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Morada</p>
                    <p class="font-medium">{{ $proposta->client->address ?: '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Informações da Proposta -->
        <div class="mb-8">
            <div class="border-b pb-2 mb-4">
                <h2 class="text-lg font-semibold text-gray-700">Detalhes da Proposta</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Data da Proposta</p>
                    <p class="font-medium">{{ \Carbon\Carbon::parse($proposta->proposal_date)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Data de Validade</p>
                    <p class="font-medium">{{ \Carbon\Carbon::parse($proposta->validity)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <span class="px-2 py-1 text-xs rounded 
                        @if($proposta->status == 'closed') bg-green-100 text-green-800
                        @elseif($proposta->status == 'sent') bg-blue-100 text-blue-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ $proposta->status == 'closed' ? 'Fechada' : ($proposta->status == 'sent' ? 'Enviada' : 'Rascunho') }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Criado por</p>
                    <p class="font-medium">{{ $proposta->created_by->name ?? 'Admin' }}</p>
                </div>
            </div>
        </div>

        <!-- Itens da Proposta -->
        <div class="mb-8">
            <div class="border-b pb-2 mb-4">
                <h2 class="text-lg font-semibold text-gray-700">Itens da Proposta</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border">Descrição</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border">Quantidade</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 border">Preço Unit.</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 border">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proposta->lines as $line)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm border">{{ $line->article->name ?? 'Produto' }}</td>
                            <td class="px-4 py-3 text-sm text-center border">{{ $line->quantity }}</td>
                            <td class="px-4 py-3 text-sm text-right border">{{ number_format($line->unit_price, 2) }} €</td>
                            <td class="px-4 py-3 text-sm text-right border font-semibold">{{ number_format($line->quantity * $line->unit_price, 2) }} €</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 border">Nenhum item encontrado</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-4 py-3 text-right font-bold border">TOTAL:</td>
                            <td class="px-4 py-3 text-right font-bold text-blue-600 border">{{ number_format($proposta->total_value, 2) }} €</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Observações -->
        @if($proposta->notes)
        <div class="mb-8">
            <div class="border-b pb-2 mb-4">
                <h2 class="text-lg font-semibold text-gray-700">Observações</h2>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-700">{{ $proposta->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
const proposalId = {{ $proposta->id }};

function downloadPdf() {
    const proposalId = {{ $proposta->id }};
    window.open(`/api/proposals/${proposalId}/download-pdf`, '_blank');
}

function closeProposal() {
    if (confirm('Confirmar o fechamento desta proposta? Após fechada não poderá ser editada.')) {
        fetch(`/api/proposals/${proposalId}/close`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success || result.message) {
                alert('Proposta fechada com sucesso!');
                window.location.reload();
            } else {
                alert('Erro: ' + (result.message || 'Erro desconhecido'));
            }
        })
        .catch(error => alert('Erro: ' + error.message));
    }
}
</script>
@endsection