<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAutomationRules;
use Illuminate\Console\Command;

class ProcessAutomation extends Command
{
    protected $signature = 'automation:process {tenant?}';
    protected $description = 'Processa regras de automatização';

    public function handle()
    {
        $tenantId = $this->argument('tenant');
        
        $this->info('Processando regras de automatização...');
        
        dispatch_sync(new ProcessAutomationRules($tenantId));
        
        $this->info('Regras processadas com sucesso!');
    }
}