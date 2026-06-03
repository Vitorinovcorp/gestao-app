@extends('layouts.app')

@section('title', 'Detalhe do Negócio')
@section('header', 'Detalhe do Negócio')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <!-- Mensagens de Sucesso e Erro -->
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

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">{{ $deal->title }}</h2>
        <div class="flex gap-2">
            @if($deal->stage === 'won' && !$deal->invoice)
            <form action="{{ route('deals.convert-to-invoice', $deal->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    <i class="fa-solid fa-file-invoice"></i> Converter em Fatura
                </button>
            </form>
            @endif
            <a href="{{ route('deals.edit', $deal->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                <i class="fa-solid fa-pen"></i> Editar
            </a>
            <form action="{{ route('deals.destroy', $deal->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este negócio?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    <i class="fa-solid fa-trash"></i> Remover
                </button>
            </form>
        </div>
    </div>

    <!-- Detalhes do negócio -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Cliente</h3>
            <p class="text-lg font-medium">
                @if($deal->entity)
                <a href="{{ route('api.entities.show', $deal->entity->id) }}" class="text-indigo-600 hover:text-indigo-800">
                    {{ $deal->entity->name }}
                </a>
                @else
                <span class="text-gray-500">Não associado</span>
                @endif
            </p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Contacto</h3>
            <p class="text-lg font-medium">
                @if($deal->person)
                {{ $deal->person->name }}
                @else
                <span class="text-gray-500">Não associado</span>
                @endif
            </p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Valor</h3>
            <p class="text-lg font-medium text-indigo-600">{{ number_format($deal->value, 2) }} €</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Etapa</h3>
            <p class="text-lg font-medium">
                <span class="px-3 py-1 rounded-full text-sm 
                    {{ $deal->stage === 'won' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $deal->stage === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $deal->stage === 'lead' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $deal->stage === 'proposal' ? 'bg-purple-100 text-purple-800' : '' }}
                    {{ $deal->stage === 'negotiation' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $deal->stage === 'follow_up' ? 'bg-orange-100 text-orange-800' : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $deal->stage)) }}
                </span>
            </p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Probabilidade</h3>
            <p class="text-lg font-medium">{{ $deal->probability }}%</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Data prevista</h3>
            <p class="text-lg font-medium">{{ $deal->expected_close_date ? \Carbon\Carbon::parse($deal->expected_close_date)->format('d/m/Y') : 'Não definida' }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Responsável</h3>
            <p class="text-lg font-medium">{{ $deal->owner->name }}</p>
        </div>
    </div>

    <!-- Envio de Proposta -->
    <div class="border-t pt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Enviar Proposta</h3>
        <form action="{{ route('deals.send-proposal', $deal->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ficheiro da Proposta *</label>
                    <input type="file" name="proposal_file" required class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email do Cliente *</label>
                    <input type="email" name="recipient_email" value="{{ $deal->entity->email ?? '' }}" required class="w-full border rounded p-2">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem (opcional)</label>
                <textarea name="email_message" rows="3" class="w-full border rounded p-2" placeholder="Adicione uma mensagem personalizada..."></textarea>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                <i class="fa-solid fa-paper-plane"></i> Enviar Proposta
            </button>
        </form>
    </div>

    <!-- Atividades -->
    <div class="border-t pt-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Atividades</h3>
        @if($deal->activities->count() > 0)
        <div class="space-y-3">
            @foreach($deal->activities as $activity)
            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded">
                <div class="w-8 h-8 flex items-center justify-center rounded-full 
                            {{ $activity->type === 'call' ? 'bg-blue-100 text-blue-600' : '' }}
                            {{ $activity->type === 'email' ? 'bg-green-100 text-green-600' : '' }}
                            {{ $activity->type === 'meeting' ? 'bg-purple-100 text-purple-600' : '' }}
                            {{ $activity->type === 'note' ? 'bg-yellow-100 text-yellow-600' : '' }}">
                    <i class="fa-solid 
                                {{ $activity->type === 'call' ? 'fa-phone' : '' }}
                                {{ $activity->type === 'email' ? 'fa-envelope' : '' }}
                                {{ $activity->type === 'meeting' ? 'fa-people-group' : '' }}
                                {{ $activity->type === 'note' ? 'fa-pencil' : '' }}">
                    </i>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-800">{{ $activity->description }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $activity->user->name }} • {{ $activity->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-500 text-sm">Nenhuma atividade registada.</p>
        @endif
    </div>
</div>

<!-- Follow-up -->
@if($deal->stage === 'follow_up')
<div class="mt-4">
    @if($deal->follow_up_active)
    <p class="text-sm text-gray-600">Próximo email: {{ $deal->follow_up_next_send_at->format('d/m/Y H:i') }}</p>
    <form action="{{ route('deals.cancel-follow-up', $deal->id) }}" method="POST">
        @csrf
        <button type="submit" class="text-red-600 hover:text-red-800">Cancelar follow-up</button>
    </form>
    @else
    <form action="{{ route('deals.deals.activate-follow-up', $deal->id) }}" method="POST">
        @csrf
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Ativar follow-up</button>
    </form>
    @endif
</div>
@endif

@endsection