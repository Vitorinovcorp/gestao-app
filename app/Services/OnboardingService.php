<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\OnboardingTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnboardingService
{
    protected $defaultTasks = [
        [
            'key' => 'branding',
            'title' => 'Configurar Branding',
            'description' => 'Defina o logotipo, cores e informações da sua empresa.',
            'order' => 1,
        ],
        [
            'key' => 'users',
            'title' => 'Convidar Utilizadores',
            'description' => 'Adicione os primeiros membros da sua equipa.',
            'order' => 2,
        ],
        [
            'key' => 'permissions',
            'title' => 'Definir Permissões',
            'description' => 'Configure os níveis de acesso para cada utilizador.',
            'order' => 3,
        ],
        [
            'key' => 'company',
            'title' => 'Configurar Empresa',
            'description' => 'Preencha os dados fiscais e legais da sua empresa.',
            'order' => 4,
        ],
        [
            'key' => 'first_client',
            'title' => 'Adicionar Primeiro Cliente',
            'description' => 'Crie o primeiro registo de cliente no sistema.',
            'order' => 5,
        ],
        [
            'key' => 'first_article',
            'title' => 'Adicionar Primeiro Artigo',
            'description' => 'Crie o primeiro produto ou serviço no catálogo.',
            'order' => 6,
        ],
        [
            'key' => 'first_proposal',
            'title' => 'Criar Primeira Proposta',
            'description' => 'Gere a sua primeira proposta comercial.',
            'order' => 7,
        ],
    ];

    public function initializeOnboarding(Tenant $tenant): void
    {
        foreach ($this->defaultTasks as $task) {
            OnboardingTask::create([
                'tenant_id' => $tenant->id,
                'task_key' => $task['key'],
                'title' => $task['title'],
                'description' => $task['description'],
                'is_completed' => false,
                'order' => $task['order'],
            ]);
        }
    }

    public function getProgress(Tenant $tenant): array
    {
        $tasks = OnboardingTask::where('tenant_id', $tenant->id)
            ->orderBy('order')
            ->get();

        $total = $tasks->count();
        $completed = $tasks->where('is_completed', true)->count();

        return [
            'tasks' => $tasks,
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'is_complete' => $total > 0 && $completed === $total,
        ];
    }

    public function completeTask(Tenant $tenant, string $taskKey): bool
    {
        Log::info('=== OnboardingService::completeTask ===');
        Log::info('Tenant ID: ' . $tenant->id);
        Log::info('Task Key: ' . $taskKey);

        $task = OnboardingTask::where('tenant_id', $tenant->id)
            ->where('task_key', $taskKey)
            ->first();

        if (!$task) {
            Log::error('Task not found for key: ' . $taskKey);
            return false;
        }

        Log::info('Task found! ID: ' . $task->id);
        Log::info('Current is_completed: ' . ($task->is_completed ? 'true' : 'false'));

        $task->update(['is_completed' => true]);

        Log::info('After update, is_completed: ' . ($task->refresh()->is_completed ? 'true' : 'false'));

        return true;
    }

    public function isTaskCompleted(Tenant $tenant, string $taskKey): bool
    {
        return OnboardingTask::where('tenant_id', $tenant->id)
            ->where('task_key', $taskKey)
            ->where('is_completed', true)
            ->exists();
    }

    public function getNextTask(Tenant $tenant): ?OnboardingTask
    {
        return OnboardingTask::where('tenant_id', $tenant->id)
            ->where('is_completed', false)
            ->orderBy('order')
            ->first();
    }

    public function getStatus(Tenant $tenant): string
    {
        $progress = $this->getProgress($tenant);

        if ($progress['is_complete']) {
            return 'completed';
        }

        if ($progress['completed'] > 0) {
            return 'in_progress';
        }

        return 'not_started';
    }
}
