@extends('layouts.app')

@section('title', 'Onboarding')
@section('header', 'Configuração Inicial')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Bem-vindo ao {{ $tenant->name }}!</h2>
        <p class="text-gray-600">Complete as tarefas abaixo para configurar o seu ambiente.</p>
    </div>

    <div class="mb-8">
        <div class="flex justify-between text-sm text-gray-600 mb-1">
            <span>Progresso</span>
            <span>{{ $progress['completed'] }} de {{ $progress['total'] }} tarefas</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-indigo-600 h-2.5 rounded-full" 
                 style="width: {{ $progress['percentage'] }}%;"></div>
        </div>
    </div>

    <div class="space-y-3">
        @foreach($progress['tasks'] as $task)
            <div class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 flex items-center justify-center rounded-full 
                        {{ $task->is_completed ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500' }}">
                        @if($task->is_completed)
                            <i class="fa-solid fa-check text-xs"></i>
                        @else
                            <span class="text-xs font-bold">{{ $task->order }}</span>
                        @endif
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-800">{{ $task->title }}</h4>
                        <p class="text-xs text-gray-500">{{ $task->description }}</p>
                    </div>
                </div>
                
                @if(!$task->is_completed)
                    <a href="{{ route('onboarding.step', ['step' => $task->order]) }}" 
                       class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 transition">
                        Iniciar
                    </a>
                @else
                    <span class="text-xs text-green-600"><i class="fa-solid fa-check-circle"></i> Concluído</span>
                @endif
            </div>
        @endforeach
    </div>

    @if($progress['is_complete'])
        <div class="mt-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-center">
            <i class="fa-solid fa-trophy text-2xl mb-2"></i>
            <p class="font-semibold">Parabéns! Todas as tarefas estão concluídas!</p>
            <a href="{{ route('dashboard') }}" class="mt-2 inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                <i class="fa-solid fa-arrow-right"></i> Ir para o Dashboard
            </a>
        </div>
    @endif
</div>
@endsection