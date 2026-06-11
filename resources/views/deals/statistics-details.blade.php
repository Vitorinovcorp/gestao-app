@extends('layouts.app')

@section('title', 'Detalhes do Produto')
@section('header', 'Detalhes do Produto')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="mb-4">
        <a href="{{ route('deals.statistics') }}" class="text-indigo-600 hover:text-indigo-800">
            <i class="fa-solid fa-arrow-left"></i> Voltar para Estatísticas
        </a>
    </div>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">{{ $article->name }}</h2>
        <p class="text-gray-500">Referência: {{ $article->reference }}</p>
        <p class="text-gray-500">Preço unitário: {{ number_format($article->price, 2) }} €</p>
    </div>

    <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Negócios que incluem este produto</h3>

    @if($article->dealLines && $article->dealLines->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500">Negócio</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500">Cliente</th>
                    <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500">Quantidade</th>
                    <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500">Valor</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500">Estado</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500">Responsável</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($article->dealLines as $line)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('deals.show', $line->deal_id) }}" class="text-indigo-600 hover:text-indigo-800">
                            {{ $line->deal->title }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $line->deal->entity->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 text-right">{{ $line->quantity }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 text-right">{{ number_format($line->total_price, 2) }} €</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full 
                            {{ $line->deal->stage === 'won' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $line->deal->stage === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $line->deal->stage === 'lead' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $line->deal->stage === 'proposal' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $line->deal->stage === 'negotiation' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $line->deal->stage)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $line->deal->owner->name ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-8 text-gray-500">
        <p>Nenhum negócio encontrado com este produto.</p>
    </div>
    @endif
</div>
@endsection