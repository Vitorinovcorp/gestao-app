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

    <!-- Cabeçalho com ações -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">{{ $deal->title }}</h2>
        <div class="flex gap-2">
            @if($deal->stage === 'won' && !$deal->invoice)
            <form action="{{ route('deals.convert-to-invoice', $deal->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    <i class="fa-solid fa-file-invoice"></i> Converter em Fatura
                </button>
            </form>
            @endif
            <a href="{{ route('deals.edit', $deal->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                <i class="fa-solid fa-pen"></i> Editar
            </a>
            <form action="{{ route('deals.destroy', $deal->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este negócio?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    <i class="fa-solid fa-trash"></i> Remover
                </button>
            </form>
        </div>
    </div>

    <!-- Grid de Informações do Negócio -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
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
                    {{ translateStage($deal->stage) }}
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
        <div>
            <h3 class="text-sm font-medium text-gray-500">Criado em</h3>
            <p class="text-lg font-medium">{{ $deal->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Última atualização</h3>
            <p class="text-lg font-medium">{{ $deal->updated_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <!-- SEÇÃO DE PROPOSTA -->
    <div class="border-t pt-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">📄 Proposta Comercial</h3>
        
        @if($deal->proposal_file)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-file-pdf text-red-500 text-2xl"></i>
                        <div>
                            <p class="font-medium text-gray-800">{{ basename($deal->proposal_file) }}</p>
                            <p class="text-xs text-gray-500">
                                Enviado em: {{ $deal->proposal_sent_at ? \Carbon\Carbon::parse($deal->proposal_sent_at)->format('d/m/Y H:i') : 'Não enviado' }}
                                @if($deal->proposal_sent_to)
                                    • Para: {{ $deal->proposal_sent_to }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('deals.download-proposal', $deal->id) }}" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                            <i class="fa-solid fa-download"></i> Download
                        </a>
                        <button onclick="openSendProposalModal()" class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                            <i class="fa-solid fa-envelope"></i> Reenviar Proposta
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                <i class="fa-solid fa-file-pdf text-gray-400 text-4xl mb-3"></i>
                <p class="text-gray-500 mb-3">Nenhuma proposta carregada</p>
                <button onclick="document.getElementById('proposal_file_input').click()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fa-solid fa-upload"></i> Carregar Proposta
                </button>
                <input type="file" id="proposal_file_input" accept=".pdf,.doc,.docx,.xlsx,.xls" class="hidden">
                <p class="text-xs text-gray-400 mt-2">PDF, DOC, XLSX (Max 10MB)</p>
            </div>
        @endif
    </div>

    <!-- Follow-up Automático -->
    @if($deal->stage === 'follow_up')
    <div class="border-t pt-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">⏰ Follow-up Automático</h3>
        @if($deal->follow_up_active)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-gray-600">
                    <i class="fa-solid fa-clock"></i> Próximo email: 
                    <strong>{{ \Carbon\Carbon::parse($deal->follow_up_next_send_at)->format('d/m/Y H:i') }}</strong>
                </p>
                <form action="{{ route('deals.cancel-follow-up', $deal->id) }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                        <i class="fa-solid fa-stop"></i> Cancelar follow-up
                    </button>
                </form>
            </div>
        @else
            <form action="{{ route('deals.activate-follow-up', $deal->id) }}" method="POST">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fa-solid fa-play"></i> Ativar follow-up automático
                </button>
            </form>
        @endif
    </div>
    @endif

    <!-- Criação Rápida de Atividades -->
    <div class="border-t pt-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"> Criação Rápida de Atividade</h3>
        <form id="quickActivityForm" class="bg-gray-50 p-4 rounded-lg">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select id="activityType" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="call">📞 Chamada</option>
                        <option value="task">✅ Tarefa</option>
                        <option value="meeting">🤝 Reunião</option>
                        <option value="note">📝 Nota</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                    <input type="datetime-local" id="activityDate" value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
                    <select id="activityUser" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $user->id == auth()->id() ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                    <input type="text" id="activityDescription" placeholder="Breve descrição da atividade" required class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    <i class="fa-solid fa-plus"></i> Adicionar Atividade
                </button>
            </div>
        </form>
    </div>

    <!-- Cronologia -->
    <div class="border-t pt-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Cronologia</h3>
            <div class="flex gap-2">
                <select id="activityFilter" class="border rounded px-2 py-1 text-sm">
                    <option value="all">Todas</option>
                    <option value="call">Chamadas</option>
                    <option value="email">Emails</option>
                    <option value="meeting">Reuniões</option>
                    <option value="note">Notas</option>
                    <option value="invoice">Faturas</option>
                    <option value="update">Atualizações</option>
                </select>
                <button onclick="toggleTimelineView()" class="text-sm text-indigo-600 hover:text-indigo-800">
                    <i class="fa-solid fa-list"></i> Alternar Vista
                </button>
            </div>
        </div>

        <div id="timelineContainer" class="space-y-4 max-h-96 overflow-y-auto">
            @forelse($deal->activities as $activity)
                <div class="activity-item flex gap-3 p-3 border rounded-lg hover:bg-gray-50 transition cursor-pointer
                    {{ $activity->type === 'call' ? 'border-l-4 border-blue-500' : '' }}
                    {{ $activity->type === 'email' ? 'border-l-4 border-green-500' : '' }}
                    {{ $activity->type === 'meeting' ? 'border-l-4 border-purple-500' : '' }}
                    {{ $activity->type === 'note' ? 'border-l-4 border-yellow-500' : '' }}
                    {{ $activity->type === 'invoice' ? 'border-l-4 border-indigo-500' : '' }}
                    {{ $activity->type === 'update' ? 'border-l-4 border-orange-500' : '' }}"
                    data-type="{{ $activity->type }}"
                    onclick="openActivityDetail({{ $activity->id }})">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center
                            {{ $activity->type === 'call' ? 'bg-blue-100 text-blue-600' : '' }}
                            {{ $activity->type === 'email' ? 'bg-green-100 text-green-600' : '' }}
                            {{ $activity->type === 'meeting' ? 'bg-purple-100 text-purple-600' : '' }}
                            {{ $activity->type === 'note' ? 'bg-yellow-100 text-yellow-600' : '' }}
                            {{ $activity->type === 'invoice' ? 'bg-indigo-100 text-indigo-600' : '' }}
                            {{ $activity->type === 'update' ? 'bg-orange-100 text-orange-600' : '' }}">
                            <i class="fa-solid 
                                {{ $activity->type === 'call' ? 'fa-phone' : '' }}
                                {{ $activity->type === 'email' ? 'fa-envelope' : '' }}
                                {{ $activity->type === 'meeting' ? 'fa-users' : '' }}
                                {{ $activity->type === 'note' ? 'fa-pencil' : '' }}
                                {{ $activity->type === 'invoice' ? 'fa-file-invoice' : '' }}
                                {{ $activity->type === 'update' ? 'fa-pen-to-square' : '' }}">
                            </i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-800">{{ $activity->subject ?? $activity->description }}</h4>
                                <p class="text-sm text-gray-500">{{ $activity->user->name ?? 'Sistema' }} • {{ $activity->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="event.stopPropagation(); editActivity({{ $activity->id }})" class="text-sm text-blue-600 hover:text-blue-800">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button onclick="event.stopPropagation(); deleteActivity({{ $activity->id }})" class="text-sm text-red-600 hover:text-red-800">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @if($activity->body)
                            <div class="mt-2 text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($activity->body, 150) }}</div>
                        @endif
                        @if($activity->metadata && is_array($activity->metadata) && isset($activity->metadata['attachments']))
                            <div class="mt-2 flex gap-2">
                                @foreach($activity->metadata['attachments'] as $attachment)
                                    <span class="text-xs text-blue-600"><i class="fa-solid fa-paperclip"></i> {{ $attachment }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fa-solid fa-timeline text-3xl mb-2"></i>
                    <p>Nenhuma atividade registada</p>
                    <p class="text-sm">Utilize o formulário acima para adicionar a primeira atividade</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- MODAL DE ENVIO DE PROPOSTA -->
<div id="sendProposalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fa-solid fa-paper-plane text-indigo-600"></i> Enviar Proposta
            </h3>
            <button onclick="closeSendProposalModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        
        <form id="sendProposalForm" action="{{ route('deals.send-proposal', $deal->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email do Destinatário *</label>
                    <input type="email" name="recipient_email" value="{{ $deal->entity->email ?? '' }}" required
                           class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assunto</label>
                    <input type="text" name="subject" value="Proposta Comercial - {{ $deal->title }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem</label>
                    <textarea name="email_message" rows="6" class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Prezado(a) {{ $deal->entity->name ?? 'Cliente' }},

Segue em anexo a proposta comercial referente ao projeto &quot;{{ $deal->title }}&quot;.

Fico à disposição para esclarecer qualquer dúvida.

Atenciosamente,
{{ auth()->user()->name }}"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Anexo (opcional)</label>
                    <input type="file" name="proposal_file" accept=".pdf,.doc,.docx,.xlsx,.xls"
                           class="w-full border rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">Deixe em branco para usar o ficheiro já carregado</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                <button type="button" onclick="closeSendProposalModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    <i class="fa-solid fa-paper-plane"></i> Enviar Proposta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DE DETALHES DA ATIVIDADE -->
<div id="activityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="activityModalTitle">Detalhes da Atividade</h3>
            <button onclick="closeActivityModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div id="activityModalBody">
            <!-- Conteúdo carregado via JavaScript -->
        </div>
    </div>
</div>

<script>
// Upload de proposta
document.getElementById('proposal_file_input')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const formData = new FormData();
        formData.append('proposal_file', file);
        
        fetch('{{ route("deals.upload-proposal", $deal->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao carregar ficheiro: ' + (data.message || 'Tente novamente'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar ficheiro.');
        });
    }
});

// Modal de envio de proposta
function openSendProposalModal() {
    document.getElementById('sendProposalModal').classList.remove('hidden');
}

function closeSendProposalModal() {
    document.getElementById('sendProposalModal').classList.add('hidden');
}

// Filtrar atividades
document.getElementById('activityFilter').addEventListener('change', function() {
    const type = this.value;
    document.querySelectorAll('.activity-item').forEach(item => {
        if (type === 'all' || item.dataset.type === type) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
});

// Alternar vista da timeline
function toggleTimelineView() {
    document.querySelectorAll('.activity-item').forEach(item => {
        item.classList.toggle('p-3');
        item.classList.toggle('p-2');
    });
}

// Modal de detalhes da atividade
function openActivityDetail(id) {
    fetch(`/api/deal-activities/${id}`)
        .then(response => response.json())
        .then(activity => {
            document.getElementById('activityModalTitle').textContent = activity.subject || activity.description || 'Detalhes';
            document.getElementById('activityModalBody').innerHTML = `
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <i class="fa-solid ${activity.type === 'call' ? 'fa-phone' : activity.type === 'email' ? 'fa-envelope' : activity.type === 'meeting' ? 'fa-users' : 'fa-pencil'} text-indigo-600"></i>
                        </div>
                        <div>
                            <p class="font-medium">${activity.type_label || activity.type}</p>
                            <p class="text-xs text-gray-500">${new Date(activity.created_at).toLocaleString('pt-PT')}</p>
                        </div>
                    </div>
                    <div>
                        <strong>Descrição:</strong>
                        <p class="text-gray-700 mt-1">${activity.description}</p>
                    </div>
                    ${activity.body ? `<div><strong>Corpo/Nota:</strong><p class="text-gray-700 mt-1">${activity.body}</p></div>` : ''}
                    <div>
                        <strong>Criado por:</strong> ${activity.user?.name || 'Sistema'}
                    </div>
                    ${activity.metadata ? `<div><strong>Metadados:</strong><pre class="text-xs bg-gray-100 p-2 rounded mt-1">${JSON.stringify(activity.metadata, null, 2)}</pre></div>` : ''}
                </div>
            `;
            document.getElementById('activityModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar detalhes da atividade.');
        });
}

function closeActivityModal() {
    document.getElementById('activityModal').classList.add('hidden');
}

// Criação rápida de atividades
document.getElementById('quickActivityForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        type: document.getElementById('activityType').value,
        scheduled_at: document.getElementById('activityDate').value,
        user_id: document.getElementById('activityUser').value,
        description: document.getElementById('activityDescription').value,
        subject: document.getElementById('activityDescription').value
    };
    
    fetch(`/deals/{{ $deal->id }}/activities`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('activityDescription').value = '';
            window.location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Tente novamente'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao criar atividade.');
    });
});

// Editar atividade
function editActivity(id) {
    fetch(`/api/deal-activities/${id}`)
        .then(response => response.json())
        .then(activity => {
            const newDescription = prompt('Nova descrição:', activity.description);
            if (newDescription !== null) {
                updateActivity(id, { description: newDescription });
            }
        });
}

function updateActivity(id, data) {
    fetch(`/deals/{{ $deal->id }}/activities/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            window.location.reload();
        } else {
            alert('Erro: ' + result.message);
        }
    });
}

// Excluir atividade
function deleteActivity(id) {
    if (confirm('Tem certeza que deseja eliminar esta atividade?')) {
        fetch(`/deals/{{ $deal->id }}/activities/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                window.location.reload();
            } else {
                alert('Erro: ' + result.message);
            }
        });
    }
}
</script>
@endsection