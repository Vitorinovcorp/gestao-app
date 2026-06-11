<?php

namespace App\Jobs;

use App\Models\AutomationRule;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\AutomationLog;
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

    protected $tenantId;

    public function __construct($tenantId = null)
    {
        $this->tenantId = $tenantId;
    }

    public function handle()
    {
        $query = AutomationRule::where('is_active', true);

        if ($this->tenantId) {
            $query->where('tenant_id', $this->tenantId);
        }

        $rules = $query->get();

        foreach ($rules as $rule) {
            $this->processRule($rule);
        }
    }

    protected function processRule(AutomationRule $rule)
    {
        $deals = Deal::where('tenant_id', $rule->tenant_id)
            ->where('stage', '!=', 'won')
            ->where('stage', '!=', 'lost')
            ->get();

        foreach ($deals as $deal) {
            // Verificar dias sem atividade
            $lastActivity = $deal->activities()->latest()->first();
            $daysWithoutActivity = $lastActivity 
                ? now()->diffInDays($lastActivity->created_at)
                : now()->diffInDays($deal->created_at);

            // Verificar se atingiu o limite
            if ($daysWithoutActivity >= $rule->inactivity_days) {
                $this->applyRule($rule, $deal, $daysWithoutActivity);
            }
        }
    }

    protected function applyRule(AutomationRule $rule, Deal $deal, $daysWithoutActivity)
    {
        // Verificar se já foi aplicado nas últimas 24h
        $lastLog = AutomationLog::where('automation_rule_id', $rule->id)
            ->where('deal_id', $deal->id)
            ->where('created_at', '>', now()->subHours(24))
            ->first();

        if ($lastLog) {
            return;
        }

        try {
            // Criar atividade
            $activity = DealActivity::create([
                'deal_id' => $deal->id,
                'user_id' => $deal->owner_id,
                'type' => $rule->activity_type,
                'description' => $this->generateActivityDescription($rule, $deal, $daysWithoutActivity),
                'scheduled_at' => now(),
                'subject' => $rule->name,
                'priority' => $rule->activity_priority,
            ]);

            // Criar evento no calendário
            CalendarEvent::create([
                'title' => "⏰ {$rule->name}: {$deal->title}",
                'description' => "Este negócio está sem atividade há {$daysWithoutActivity} dias.\n\n" .
                    "Ação sugerida: {$activity->description}",
                'start_datetime' => now()->addDay(),
                'user_id' => $deal->owner_id,
                'deal_id' => $deal->id,
                'tenant_id' => $rule->tenant_id,
                'type' => 'automation',
            ]);

            // Registrar log
            AutomationLog::create([
                'automation_rule_id' => $rule->id,
                'deal_id' => $deal->id,
                'status' => 'success',
                'message' => "Atividade criada automaticamente após {$daysWithoutActivity} dias sem atividade",
                'created_activity_id' => $activity->id,
            ]);

            Log::info("Automação aplicada: {$rule->name} no deal {$deal->id}");

            // Se tiver notificação, criar notificação (opcional)
            if ($rule->send_notification) {
                $this->createNotification($deal, $rule, $daysWithoutActivity);
            }

        } catch (\Exception $e) {
            Log::error("Erro ao aplicar automação: " . $e->getMessage());

            AutomationLog::create([
                'automation_rule_id' => $rule->id,
                'deal_id' => $deal->id,
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function generateActivityDescription($rule, $deal, $daysWithoutActivity)
    {
        $descriptions = [
            'call' => "📞 Entrar em contacto com o cliente sobre o negócio '{$deal->title}'. Está sem atualização há {$daysWithoutActivity} dias.",
            'task' => "✅ Revisar o negócio '{$deal->title}' e agendar follow-up. Sem atividade há {$daysWithoutActivity} dias.",
            'meeting' => "🤝 Agendar reunião para discutir o andamento do negócio '{$deal->title}'.",
            'note' => "📝 Adicionar nota sobre o negócio '{$deal->title}' e plano de ação.",
        ];

        return $descriptions[$rule->activity_type] ?? "Revisar negócio '{$deal->title}' - {$daysWithoutActivity} dias sem atividade.";
    }

    protected function createNotification($deal, $rule, $daysWithoutActivity)
    {
        // Criar notificação (implementar se tiver sistema de notificações)
        // Notification::send($deal->owner, new AutomationNotification($deal, $rule, $daysWithoutActivity));
    }
}