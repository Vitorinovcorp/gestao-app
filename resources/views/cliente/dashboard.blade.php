@extends('layouts.cliente')

@section('title', 'Dashboard')
@section('content')
<div class="space-y-6">
    <!-- Banner de boas-vindas -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
        <h1 class="text-2xl font-bold">Bem-vindo, {{ $user->name ?? 'Cliente' }}!</h1>
        <p class="text-blue-100 mt-2">Acompanhe aqui as suas propostas e encomendas.</p>
    </div>

    <!-- Cards de estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Propostas</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalPropostas ?? 0 }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <a href="{{ route('cliente.propostas') }}" class="mt-4 inline-block text-blue-600 text-sm hover:underline">Ver detalhes →</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Encomendas</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalEncomendas ?? 0 }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
            <a href="{{ route('cliente.encomendas') }}" class="mt-4 inline-block text-green-600 text-sm hover:underline">Ver detalhes →</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Gasto</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalGasto ?? 0, 2) }}€</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas propostas -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h2 class="font-semibold text-gray-800">Últimas Propostas</h2>
        </div>
        <div class="divide-y">
            @forelse($ultimasPropostas ?? [] as $proposta)
            <div class="p-4 flex justify-between items-center">
                <div>
                    <p class="font-medium">{{ $proposta->number ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-500">{{ $proposta->created_at ?? '' }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="font-semibold">{{ number_format($proposta->total_value ?? 0, 2) }}€</span>
                    <a href="{{ route('cliente.propostas.show', $proposta->id ?? 0) }}" class="text-blue-600 text-sm hover:underline">Ver</a>
                </div>
            </div>
            @empty
            <div class="p-4 text-center text-gray-500">Nenhuma proposta encontrada</div>
            @endforelse
        </div>
    </div>
</div>
@endsection