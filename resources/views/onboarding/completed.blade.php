@extends('layouts.app')

@section('title', 'Onboarding Concluído')
@section('header', 'Parabéns!')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto text-center">
    <div class="mb-6">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-check-circle text-green-500 text-4xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Onboarding Concluído!</h2>
        <p class="text-gray-600 mt-2">
            Parabéns! Você completou todas as tarefas de configuração do seu tenant.
            Agora pode começar a utilizar o sistema.
        </p>
    </div>

    <div class="flex justify-center gap-4">
        <a href="{{ route('dashboard') }}" 
           class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            <i class="fa-solid fa-arrow-right"></i> Ir para o Dashboard
        </a>
        <a href="{{ route('tenants.index') }}" 
           class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            <i class="fa-solid fa-list"></i> Ver Tenants
        </a>
    </div>
</div>
@endsection