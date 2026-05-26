<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    /**
     * Cria uma nova subscrição para um tenant
     */
    public function createSubscription(Tenant $tenant, Plan $plan, array $options = []): Subscription
    {
        return DB::transaction(function () use ($tenant, $plan, $options) {
            $trialDays = $options['trial_days'] ?? config('subscription.trial_days', 14);
            $trialEndsAt = now()->addDays($trialDays);

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'trial',
                'starts_at' => now(),
                'trial_ends_at' => $trialEndsAt,
                'next_billing_at' => $trialEndsAt->copy()->addMonth(),
                'auto_renew' => true,
                'meta' => $options['meta'] ?? [],
            ]);

            // Inicializar o uso dos limites
            if ($plan->limits) {
                foreach ($plan->limits as $feature => $limit) {
                    $subscription->usage()->create([
                        'feature' => $feature,
                        'used' => 0,
                        'limit' => $limit,
                        'reset_at' => $subscription->next_billing_at,
                    ]);
                }
            }

            return $subscription;
        });
    }

    public function changePlan(Subscription $subscription, Plan $newPlan, string $action = 'upgrade'): Subscription
    {
        // Atualizar o plano diretamente
        $subscription->plan_id = $newPlan->id;
        $subscription->save();

        // Forçar o refresh
        $subscription->refresh();

        // Registrar no log (opcional)
        $subscription->logs()->create([
            'old_plan_id' => $subscription->plan->id,
            'new_plan_id' => $newPlan->id,
            'action' => $action,
            'notes' => "{$action} de {$subscription->plan->name} para {$newPlan->name}",
        ]);

        return $subscription;
    }
    protected function handleUpgradeProrata(Subscription $subscription, Plan $oldPlan, Plan $newPlan): void
    {
        $daysRemaining = now()->diffInDays($subscription->next_billing_at);
        $totalDays = now()->diffInDays($subscription->next_billing_at->copy()->subMonth());
        $prorata = ($daysRemaining / $totalDays) * ($newPlan->price - $oldPlan->price);

        // Registrar o valor no meta
        $meta = $subscription->meta ?? [];
        $meta['prorata_amount'] = round($prorata, 2);
        $meta['prorata_days'] = $daysRemaining;
        $subscription->update(['meta' => $meta]);
    }

    public function syncLimits(Subscription $subscription): void
    {
        $newPlan = $subscription->plan;

        // Remover usos de features que não existem mais
        $subscription->usage()->whereNotIn('feature', array_keys($newPlan->limits ?? []))->delete();

        // Adicionar ou atualizar limites
        if ($newPlan->limits) {
            foreach ($newPlan->limits as $feature => $limit) {
                $usage = $subscription->usage()->where('feature', $feature)->first();
                if ($usage) {
                    $usage->update(['limit' => $limit]);
                } else {
                    $subscription->usage()->create([
                        'feature' => $feature,
                        'used' => 0,
                        'limit' => $limit,
                        'reset_at' => $subscription->next_billing_at,
                    ]);
                }
            }
        }
    }

    /**
     * Cancela a subscrição
     */
    public function cancelSubscription(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'auto_renew' => false,
            ]);

            // Registrar no log
            $subscription->logs()->create([
                'action' => 'cancel',
                'notes' => 'Subscrição cancelada pelo utilizador',
            ]);
        });
    }

    /**
     * Renova a subscrição
     */
    public function renewSubscription(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $subscription->update([
                'next_billing_at' => $subscription->next_billing_at->addMonth(),
            ]);

            // Resetar os limites
            foreach ($subscription->usage as $usage) {
                $usage->update(['used' => 0, 'reset_at' => $subscription->next_billing_at]);
            }

            // Registrar no log
            $subscription->logs()->create([
                'action' => 'renew',
                'notes' => 'Subscrição renovada automaticamente',
            ]);
        });
    }

    /**
     * Verifica o status do trial
     */
    public function checkTrialStatus(Subscription $subscription): void
    {
        if ($subscription->isTrial() && now()->greaterThan($subscription->trial_ends_at)) {
            $subscription->update(['status' => 'active']);
        }
    }

    public function getDashboardData(Subscription $subscription): array
    {
        $this->checkTrialStatus($subscription);

        $usageData = [];
        foreach ($subscription->usage as $usage) {
            $usageData[$usage->feature] = [
                'used' => $usage->used,
                'limit' => $usage->limit,
                'percentage' => $usage->limit > 0 ? round(($usage->used / $usage->limit) * 100) : 0,
            ];
        }

        return [
            'subscription' => $subscription,
            'plan' => Plan::find($subscription->plan_id),
            'usage' => $usageData,
            'is_trial' => $subscription->isTrial(),
            'trial_days_left' => $subscription->isTrial() ? round(now()->diffInDays($subscription->trial_ends_at)) : 0, // ARREDONDADO
            'status' => $subscription->status,
        ];
    }
}
