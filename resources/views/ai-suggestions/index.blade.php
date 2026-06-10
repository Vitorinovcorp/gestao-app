@extends('layouts.app')

@section('title', 'Sugestões AI Inovcorp')
@section('header', 'Sugestões AI Inovcorp')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Sugestões do Agente AI</h2>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($suggestions->count() > 0)
        <div class="space-y-3">
            @foreach($suggestions as $suggestion)
                <div class="border rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $suggestion->title }}</h3>
                            <p class="text-sm text-gray-600">{{ $suggestion->description }}</p>
                            @if($suggestion->reason)
                                <p class="text-xs text-gray-500 mt-1">{{ $suggestion->reason }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-2">
                                Negócio: <a href="{{ route('deals.show', $suggestion->deal_id) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $suggestion->deal->title ?? 'N/A' }}
                                </a>
                                • Sugerido: {{ $suggestion->suggested_at ? $suggestion->suggested_at->format('d/m/Y H:i') : 'Agora' }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <form action="{{ route('ai-suggestions.accept', $suggestion->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                    <i class="fa-solid fa-check"></i> Aceitar
                                </button>
                            </form>
                            <form action="{{ route('ai-suggestions.dismiss', $suggestion->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700">
                                    <i class="fa-solid fa-xmark"></i> Ignorar
                                </button>
                            </form>
                            <form action="{{ route('ai-suggestions.archive', $suggestion->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                                    <i class="fa-solid fa-archive"></i> Arquivar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <i class="fa-solid fa-robot text-gray-400 text-4xl mb-3"></i>
            <p class="text-gray-500">Nenhuma sugestão disponível no momento.</p>
        </div>
    @endif
</div>
@endsection