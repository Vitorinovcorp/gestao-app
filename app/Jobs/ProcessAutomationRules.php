<?php

namespace App\Jobs;

use App\Models\AutomationRule;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAutomationRules implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $rules = AutomationRule::where('is_active', true)->get();

        foreach ($rules as $rule) {
            $this->processRule($rule);
        }
    }

    protected function processRule(AutomationRule $rule)
    {
        $tenantId = $rule->tenant_id;

        switch ($rule->trigger_type) {
            case 'inactivity_days':
                $this->processInactivityRule($rule, $tenantId);
                break;
            default:
                Log::warning('Tipo de gatilho não suportado: ' . $rule->trigger_type);
        }
    }

    protected function processInactivityRule(AutomationRule $rule, $tenantId)
    {
        $days = $rule->conditions['days'] ?? 5;
        $stage = $rule->conditions['stage'] ?? null;

        $query = Deal::where('tenant_id', $tenantId);

        if ($stage) {
            $query->where('stage', $stage);
        }

        $deals = $query->get();

        foreach ($deals as $deal) {
            $lastActivity = $deal->activities()->latest()->first();

            if (!$lastActivity || $lastActivity->created_at < now()->subDays($days)) {
                $this->createAutomatedActivity($deal, $rule);
            }
        }
    }

    protected function createAutomatedActivity(Deal $deal, AutomationRule $rule)
    {
        $config = $rule->action_config;

        $activity = DealActivity::create([
            'deal_id' => $deal->id,
            'type' => $config['activity_type'] ?? 'task',
            'description' => $config['description'] ?? "Atividade automatizada: {$rule->name}",
            'user_id' => $deal->owner_id,
            'scheduled_at' => now()->addDays($config['days_offset'] ?? 0),
        ]);

        // Criar evento no calendário
        CalendarEvent::create([
            'title' => $config['event_title'] ?? "Follow-up: {$deal->title}",
            'description' => $config['event_description'] ?? "Atividade gerada automaticamente pela regra: {$rule->name}",
            'start_datetime' => now()->addDays($config['days_offset'] ?? 0),
            'end_datetime' => now()->addDays($config['days_offset'] ?? 0)->addHour(),
            'entity_id' => $deal->entity_id,
            'user_id' => $deal->owner_id,
            'status' => 'scheduled',
            'priority' => $config['priority'] ?? 'medium',
            'tenant_id' => $deal->tenant_id,
        ]);

        Log::info('Atividade automatizada criada para o negócio: ' . $deal->title);
    }
}