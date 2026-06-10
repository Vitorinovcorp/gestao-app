<?php

namespace App\Listeners;

use App\Events\ActivityCreated;
use App\Jobs\ProcessAISuggestions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AnalyzeNewActivity implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ActivityCreated $event): void
    {
        $deal = $event->activity->deal;
        
        if ($deal) {
            dispatch(new ProcessAISuggestions($deal->tenant_id));
        }
    }
}