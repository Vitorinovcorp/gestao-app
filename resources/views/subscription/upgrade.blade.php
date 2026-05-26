@extends('layouts.app')

@section('title', 'Mudar Plano')
@section('header', 'Mudar Plano')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Mudar Plano</h2>
        <p class="text-gray-600">Selecione um novo plano para a sua subscrição.</p>
    </div>

    <!-- Plano Atual -->
    <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
        <h3 class="text-sm font-medium text-gray-500">Plano Atual</h3>
        <p class="text-xl font-bold text-indigo-700">{{ $currentPlan->name }}</p>
        <p class="text-sm text-gray-600">{{ number_format($currentPlan->price, 2) }} €/mês</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            <div class="border rounded-lg p-6 hover:shadow-lg transition {{ $plan->id === $currentPlan->id ? 'ring-2 ring-indigo-500' : '' }}">
                <div class="flex justify-between items-start">
                    <h3 class="text-xl font-bold text-gray-800">{{ $plan->name }}</h3>
                    @if((int)$plan->id === (int)$currentPlan->id)
                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs rounded">Atual</span>
                    @endif
                </div>
                <p class="text-3xl font-bold text-indigo-600 mt-2">{{ number_format($plan->price, 2) }} €</p>
                <p class="text-sm text-gray-500">{{ $plan->description }}</p>
                
                <ul class="mt-4 space-y-2">
                    @foreach($plan->limits as $key => $limit)
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fa-solid fa-check text-green-500 mr-2"></i>
                            {{ ucfirst($key) }}: {{ $limit }}
                        </li>
                    @endforeach
                </ul>

                @if($plan->id !== $currentPlan->id)
                    <a href="{{ route('mudar.plano', $plan->id) }}" class="w-full px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 block text-center" onclick="return confirm('Tem certeza que deseja mudar de plano?')">
                        @if($plan->price > $currentPlan->price)
                            <i class="fa-solid fa-arrow-up"></i> Upgrade (pró-rata)
                        @else
                            <i class="fa-solid fa-arrow-down"></i> Downgrade (próximo ciclo)
                        @endif
                    </a>
                @else
                    <button disabled class="w-full px-4 py-2 bg-gray-300 text-gray-500 rounded cursor-not-allowed">
                        Plano Atual
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Informações adicionais -->
    <div class="mt-8 p-4 bg-gray-50 border border-gray-200 rounded-lg">
        <h4 class="text-sm font-semibold text-gray-700 mb-2">Informações Importantes</h4>
        <ul class="space-y-1 text-sm text-gray-600">
            <li><i class="fa-solid fa-arrow-up text-green-500 mr-2"></i> Upgrade: Efetuado imediatamente com custo pró-rata</li>
            <li><i class="fa-solid fa-arrow-down text-yellow-500 mr-2"></i> Downgrade: Aplicado no próximo ciclo de faturação</li>
            <li><i class="fa-solid fa-calendar text-blue-500 mr-2"></i> O plano atual permanece ativo até ao fim do ciclo</li>
        </ul>
    </div>
</div>
@endsection