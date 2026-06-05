@extends('layouts.app')

@section('title', 'Negócios')
@section('header', 'Negócios')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Lista de Negócios</h2>
        <a href="{{ route('deals.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            <i class="fa-solid fa-plus"></i> Novo Negócio
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entidade</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Etapa</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prob.</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsável</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($deals as $deal)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $deal->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $deal->entity ? $deal->entity->name : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($deal->value, 2) }} €</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $deal->stage === 'won' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $deal->stage === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $deal->stage === 'lead' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $deal->stage === 'proposal' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $deal->stage === 'negotiation' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $deal->stage === 'follow_up' ? 'bg-orange-100 text-orange-800' : '' }}">
                                {{ $deal->stage_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $deal->probability }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $deal->owner->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('deals.show', $deal->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('deals.edit', $deal->id) }}" class="text-yellow-600 hover:text-yellow-900 mr-2">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('deals.destroy', $deal->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja remover este negócio?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection