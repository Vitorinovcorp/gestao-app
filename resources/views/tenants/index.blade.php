@extends('layouts.app')

@section('title', 'Meus Tenants')
@section('header', 'Meus Tenants')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Seus Tenants</h2>
        <a href="{{ route('tenants.create') }}"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fa-solid fa-plus"></i> Novo Tenant
        </a>
    </div>

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

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nome
                    </th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Slug
                    </th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Proprietário
                    </th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($tenants as $tenant)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3"
                                style="background-color: {{ $tenant->primary_color }}">
                                {{ substr($tenant->name, 0, 1) }}
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $tenant->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $tenant->slug }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $tenant->owner->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $tenant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $tenant->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <!-- ALTERADO: Formulário POST em vez de link GET -->
                        <form method="POST" action="{{ route('tenants.switch', $tenant->id) }}" class="inline-block mr-3">
                            @csrf
                            <button type="submit" class="text-indigo-600 hover:text-indigo-900">
                                <i class="fa-solid fa-arrow-right-arrow-left"></i> Alternar
                            </button>
                        </form>

                        <form method="POST" action="{{ route('tenants.destroy', $tenant->id) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja remover este tenant? Todos os dados associados serão perdidos.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fa-solid fa-trash"></i> Remover
                            </button>
                        </form>

                        <a href="{{ route('tenants.settings') }}"
                            class="text-gray-600 hover:text-gray-900">
                            <i class="fa-solid fa-gear"></i> Configurações
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection