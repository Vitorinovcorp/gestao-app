<?php

namespace App\Console\Commands;

use App\Models\Deal;
use App\Jobs\SendFollowUpEmail;
use Illuminate\Console\Command;

class ProcessFollowUps extends Command
{
    protected $signature = 'follow-up:process';
    protected $description = 'Processa os follow-ups automáticos';

    public function handle()
    {
        $deals = Deal::where('stage', 'follow_up')
            ->where('follow_up_active', true)
            ->where('follow_up_next_send_at', '<=', now())
            ->get();

        foreach ($deals as $deal) {
            SendFollowUpEmail::dispatch($deal);
        }

        $this->info("Processados {$deals->count()} follow-ups.");
    }
}