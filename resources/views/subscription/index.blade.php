@extends('layouts.app')

@section('title', 'Subscrição')
@section('header', 'Subscrição')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Subscrição</h2>
        <div class="flex gap-2">
            <a href="{{ route('subscription.plans') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                <i class="fa-solid fa-arrow-up"></i> Mudar Plano
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Plano Atual -->
        <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
            <h3 class="text-sm font-medium text-gray-500">Plano Atual</h3>
            <p class="text-2xl font-bold text-indigo-700">{{ $dashboard['plan']->name }}</p>
            <p class="text-sm text-gray-600">{{ number_format($dashboard['plan']->price, 2) }} €/mês</p>
            @if($dashboard['is_trial'])
                <span class="inline-block mt-2 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">
                    Trial - {{ $dashboard['trial_days_left'] }} dias restantes
                </span>
            @endif
        </div>

        <!-- Status -->
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <h3 class="text-sm font-medium text-gray-500">Status</h3>
            <p class="text-xl font-bold text-blue-700">
                {{ ucfirst($dashboard['status']) }}
            </p>
            @if($dashboard['subscription']->next_billing_at)
                <p class="text-sm text-gray-600">Próxima renovação: {{ $dashboard['subscription']->next_billing_at->format('d/m/Y') }}</p>
            @endif
        </div>

        <!-- Ações -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Ações</h3>
            <div class="space-y-2 mt-2">
                <a href="{{ route('subscription.plans') }}" class="block text-indigo-600 hover:text-indigo-800 text-sm">
                    <i class="fa-solid fa-arrow-up"></i> Upgrade/Downgrade
                </a>
                <form method="POST" action="{{ route('subscription.cancel') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Tem certeza que deseja cancelar?')">
                        <i class="fa-solid fa-ban"></i> Cancelar Subscrição
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Utilização dos Limites -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Utilização</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($dashboard['usage'] as $feature => $data)
                <div class="bg-white border rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">{{ ucfirst($feature) }}</span>
                        <span class="text-sm text-gray-500">{{ $data['used'] }} / {{ $data['limit'] ?? '∞' }}</span>
                    </div>
                    @if($data['limit'])
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min($data['percentage'], 100) }}%;"></div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Histórico -->
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Histórico</h3>
        <a href="{{ route('subscription.logs') }}" class="text-indigo-600 text-sm hover:text-indigo-800">
            Ver histórico de alterações
        </a>
    </div>
</div>
@endsection