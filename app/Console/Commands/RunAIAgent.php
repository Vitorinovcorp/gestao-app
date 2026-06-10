<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAISuggestions;
use Illuminate\Console\Command;

class RunAIAgent extends Command
{
    protected $signature = 'ai-agent:run {tenant_id?}';
protected $description = 'Executa o agente comercial AI';

public function handle()
{
    $tenantId = $this->argument('tenant_id');
    
    if (!$tenantId) {
        // Tentar obter o tenant do utilizador atual
        $user = \App\Models\User::find(11);
        if ($user) {
            $tenant = $user->tenants()->first();
            if ($tenant) {
                $tenantId = $tenant->id;
            }
        }
    }
    
    if (!$tenantId) {
        $this->error('Nenhum tenant ativo encontrado.');
        return;
    }
    
    ProcessAISuggestions::dispatch($tenantId);
    $this->info('Agente AI executado com sucesso.');
}
}