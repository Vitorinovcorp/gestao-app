@extends('layouts.app')

@section('title', 'Onboarding - Passo ' . $step . ' de 7')
@section('header', 'Onboarding - Passo ' . $step . ' de 7')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto">
    <div class="mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Passo {{ $step }} de 7: {{ ucfirst($taskKey) }}</h2>
        <p class="text-gray-600">Complete este passo para continuar.</p>
    </div>

    <form action="{{ route('onboarding.process', $step) }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        @switch($taskKey)
            @case('branding')
                @include('onboarding.tasks.branding')
                @break
            @case('users')
                @include('onboarding.tasks.users')
                @break
            @case('permissions')
                @include('onboarding.tasks.permissions')
                @break
            @case('company')
                @include('onboarding.tasks.company')
                @break
            @case('first_client')
                @include('onboarding.tasks.first_client')
                @break
            @case('first_article')
                @include('onboarding.tasks.first_article')
                @break
            @case('first_proposal')
                @include('onboarding.tasks.first_proposal')
                @break
        @endswitch

        <div class="flex justify-end mt-4">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Concluir e Avançar
            </button>
        </div>
    </form>
</div>
@endsection