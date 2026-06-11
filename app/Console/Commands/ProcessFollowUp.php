<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFollowUpEmails;
use Illuminate\Console\Command;

class ProcessFollowUp extends Command
{
    protected $signature = 'follow-up:process {tenant?}';
    protected $description = 'Processa envios de emails de follow-up';

    public function handle()
    {
        $tenantId = $this->argument('tenant');
        
        $this->info('Processando follow-ups...');
        
        dispatch_sync(new ProcessFollowUpEmails($tenantId));
        
        $this->info('Follow-ups processados com sucesso!');
    }
}