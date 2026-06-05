<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAutomationRules;
use Illuminate\Console\Command;

class RunAutomationRules extends Command
{
    protected $signature = 'automation:run';
    protected $description = 'Executa as regras de automatização';

    public function handle()
    {
        ProcessAutomationRules::dispatch();
        $this->info('Regras de automatização processadas.');
    }
}