@extends('layouts.app')

@section('title', 'Nova Regra de Automatização')
@section('header', 'Nova Regra de Automatização')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('automation.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Regra *</label>
            <input type="text" name="name" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Gatilho *</label>
            <select name="trigger_type" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="inactivity_days">Inatividade (dias)</option>
                <option value="stage_change">Mudança de Estado</option>
                <option value="value_threshold">Limite de Valor</option>
                <option value="days_until_close">Dias até Fecho</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Dias de Inatividade *</label>
            <input type="number" name="days" value="5" required min="1" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4" id="value_threshold_fields" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Limite</label>
            <input type="number" step="0.01" name="value_threshold" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4" id="stage_change_fields" style="display: none;">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">De Estado</label>
                    <select name="from_stage" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Selecione</option>
                        <option value="proposal">Proposta</option>
                        <option value="negotiation">Negociação</option>
                        <option value="follow_up">Atualização</option>
                        <option value="won">Ganho</option>
                        <option value="lost">Perdido</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Para Estado</label>
                    <select name="to_stage" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Selecione</option>
                        <option value="proposal">Proposta</option>
                        <option value="negotiation">Negociação</option>
                        <option value="follow_up">Atualização</option>
                        <option value="won">Ganho</option>
                        <option value="lost">Perdido</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-4" id="days_until_close_fields" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-1">Dias até Fecho</label>
            <input type="number" name="days_until_close" value="7" min="1" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Atividade *</label>
            <select name="activity_type" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="call">Chamada</option>
                <option value="task">Tarefa</option>
                <option value="meeting">Reunião</option>
                <option value="note">Nota</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
            <select name="priority" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="low">Baixa</option>
                <option value="medium" selected>Média</option>
                <option value="high">Alta</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Dias para Atividade</label>
            <input type="number" name="days_offset" value="0" min="0" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('automation.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Criar Regra
            </button>
        </div>
    </form>
</div>

<script>
document.querySelector('select[name="trigger_type"]').addEventListener('change', function() {
    const value = this.value;
    
    // Remover required de todos os campos opcionais
    document.querySelectorAll('#value_threshold_fields input, #days_until_close_fields input, #stage_change_fields select').forEach(el => {
        el.removeAttribute('required');
    });
    
    // Esconder todos os campos opcionais
    document.getElementById('stage_change_fields').style.display = 'none';
    document.getElementById('value_threshold_fields').style.display = 'none';
    document.getElementById('days_until_close_fields').style.display = 'none';
    
    // Mostrar campos conforme o tipo e adicionar required
    if (value === 'stage_change') {
        document.getElementById('stage_change_fields').style.display = 'block';
        document.querySelectorAll('#stage_change_fields select').forEach(el => {
            el.setAttribute('required', 'required');
        });
    } else if (value === 'value_threshold') {
        document.getElementById('value_threshold_fields').style.display = 'block';
        document.querySelector('#value_threshold_fields input').setAttribute('required', 'required');
    } else if (value === 'days_until_close') {
        document.getElementById('days_until_close_fields').style.display = 'block';
        document.querySelector('#days_until_close_fields input').setAttribute('required', 'required');
    }
});
</script>
@endsection