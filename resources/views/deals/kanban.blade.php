@extends('layouts.app')

@section('title', 'Kanban de Negócios')
@section('header', 'Kanban de Negócios')

@section('content')
<div class="p-4">
    <!-- Filtros Avançados -->
    <form method="GET" action="{{ route('deals.kanban') }}" class="mb-4 bg-white p-4 rounded-lg shadow flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700">Responsável</label>
            <select name="owner_id" class="border rounded px-3 py-1" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('owner_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Data Prevista</label>
            <input type="date" name="expected_close_date" value="{{ request('expected_close_date') }}" class="border rounded px-3 py-1" onchange="this.form.submit()">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Valor Mínimo</label>
            <input type="number" name="min_value" value="{{ request('min_value') }}" class="border rounded px-3 py-1 w-24" onchange="this.form.submit()">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Valor Máximo</label>
            <input type="number" name="max_value" value="{{ request('max_value') }}" class="border rounded px-3 py-1 w-24" onchange="this.form.submit()">
        </div>
        <div>
            <button type="submit" class="px-4 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">Filtrar</button>
            <a href="{{ route('deals.kanban') }}" class="px-4 py-1 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 ml-2">Limpar</a>
        </div>
    </form>

    <div class="mb-4 flex justify-between items-center">
        <h2 class="text-xl font-bold">Pipeline de Negócios</h2>
        <a href="{{ route('deals.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            <i class="fa-solid fa-plus"></i> Novo Negócio
        </a>
    </div>

    <div class="flex gap-4 overflow-x-auto pb-4 min-h-[400px]">
        @foreach(['lead', 'proposal', 'negotiation', 'follow_up', 'won', 'lost'] as $stage)
            <div class="min-w-[200px] min-h-[200px] bg-gray-100 rounded-lg p-3 flex flex-col">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-gray-700">{{ $stageLabels[$stage] }}</h3>
                    <span class="text-xs bg-gray-200 px-2 py-1 rounded">{{ $deals->where('stage', $stage)->count() }}</span>
                </div>

                <div class="space-y-3 flex-1 overflow-y-auto" id="column-{{ $stage }}">
                    @foreach($deals->where('stage', $stage) as $deal)
                        <div class="bg-white p-3 rounded shadow hover:shadow-md transition cursor-move"
                             draggable="true"
                             data-deal-id="{{ $deal->id }}"
                             data-stage="{{ $deal->stage }}"
                             ondragstart="handleDragStart(event, {{ $deal->id }})">
                            <div class="flex justify-between items-start">
                                <h4 class="font-medium text-gray-800">{{ $deal->title }}</h4>
                                <span class="text-xs font-semibold px-2 py-1 rounded 
                                    {{ $deal->probability >= 70 ? 'bg-green-100 text-green-700' : 
                                       ($deal->probability >= 40 ? 'bg-yellow-100 text-yellow-700' : 
                                       'bg-red-100 text-red-700') }}">
                                    {{ $deal->probability }}%
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ number_format($deal->value, 2) }} €</p>
                            <div class="flex items-center justify-between mt-2 text-xs text-gray-400">
                                <span><i class="fa-solid fa-user mr-1"></i> {{ $deal->owner->name }}</span>
                                @if($deal->expected_close_date)
                                    <span><i class="fa-solid fa-calendar mr-1"></i> {{ $deal->expected_close_date ? \Carbon\Carbon::parse($deal->expected_close_date)->format('d/m/Y') : '-' }}</span>
                                @endif
                            </div>
                            <div class="mt-2 pt-2 border-t border-gray-100 flex gap-2">
                                <a href="{{ route('deals.show', $deal->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800">
                                    <i class="fa-solid fa-eye"></i> Detalhes
                                </a>
                                <a href="{{ route('deals.edit', $deal->id) }}" class="text-xs text-gray-600 hover:text-gray-800">
                                    <i class="fa-solid fa-pen"></i> Editar
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
function handleDragStart(event, dealId) {
    event.dataTransfer.setData('dealId', dealId);
}

document.querySelectorAll('[id^="column-"]').forEach(column => {
    column.addEventListener('dragover', (e) => {
        e.preventDefault();
    });

    column.addEventListener('drop', (e) => {
        e.preventDefault();
        const dealId = event.dataTransfer.getData('dealId');
        const newStage = column.id.replace('column-', '');

        const card = document.querySelector(`[data-deal-id="${dealId}"]`);
        column.appendChild(card);
        card.dataset.stage = newStage;

        fetch(`/deals/${dealId}/move`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ stage: newStage })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });
});
</script>
@endsection