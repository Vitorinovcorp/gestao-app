@extends('layouts.app')

@section('content')
<div class="container mx-auto py-10">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Logs do Sistema</h1>
        <div class="flex gap-2">
            <button type="button" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm" onclick="confirmClearLogs()">
                 Limpar Logs 
            </button>
            <a href="{{ route('logs.export') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                 Exportar CSV
            </a>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" action="{{ route('logs.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Menu</label>
                <select name="menu" class="w-full border rounded px-3 py-2">
                    <option value="">Todos</option>
                    @if(isset($menus))
                        @foreach($menus as $menu)
                            <option value="{{ $menu }}" {{ request('menu') == $menu ? 'selected' : '' }}>
                                {{ $menu }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Utilizador</label>
                <select name="user_id" class="w-full border rounded px-3 py-2">
                    <option value="">Todos</option>
                    @if(isset($users))
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Data Início</label>
                <input type="date" name="date_from" class="w-full border rounded px-3 py-2" value="{{ request('date_from') }}">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Data Fim</label>
                <input type="date" name="date_to" class="w-full border rounded px-3 py-2" value="{{ request('date_to') }}">
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                     Filtrar
                </button>
                <a href="{{ route('logs.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                   Limpar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Tabela de Logs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold">ID</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold">Data</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold">Hora</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold">Utilizador</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold">Menu</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold">Acção</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold">Dispositivo</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($logs) && count($logs) > 0)
                        @foreach($logs as $log)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 text-sm">{{ $log['id'] ?? '' }}</td>
                            <td class="py-2 px-4 text-sm">{{ $log['data'] ?? '' }}</td>
                            <td class="py-2 px-4 text-sm">{{ $log['hora'] ?? '' }}</td>
                            <td class="py-2 px-4 text-sm">
                                @if(($log['utilizador'] ?? '') != 'Sistema')
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                        <i class="fas fa-user"></i> {{ $log['utilizador'] ?? 'Sistema' }}
                                    </span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">
                                        <i class="fas fa-robot"></i> {{ $log['utilizador'] ?? 'Sistema' }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 px-4 text-sm">
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">{{ $log['menu'] ?? 'Geral' }}</span>
                            </td>
                            <td class="py-2 px-4 text-sm">{{ $log['acao'] ?? '' }}</td>
                            <td class="py-2 px-4 text-sm text-gray-600">
                                <small>{{ Str::limit($log['dispositivo'] ?? 'Desconhecido', 40) }}</small>
                            </td>
                            <td class="py-2 px-4 text-sm font-mono">{{ $log['ip'] ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 block"></i>
                                Nenhum log encontrado no sistema.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        @if(isset($logs) && method_exists($logs, 'links'))
            <div class="py-4 px-6 border-t">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function confirmClearLogs() {
        const days = prompt('Limpar logs com mais de quantos dias? (padrão: 90)', '90');
        
        if (days && confirm(`Tem certeza que deseja limpar logs com mais de ${days} dias?\n\nEsta ação não pode ser desfeita.`)) {
            const formData = new FormData();
            formData.append('days', days);
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'DELETE');
            
            fetch('{{ route("logs.clear-old") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Erro ao limpar logs.');
                }
            })
            .catch(error => {
                alert('Erro ao limpar logs: ' + error);
            });
        }
    }
</script>
@endpush
@endsection