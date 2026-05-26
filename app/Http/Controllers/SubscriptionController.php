<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\SubscriptionService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;
    protected TenantService $tenantService;

    public function __construct(SubscriptionService $subscriptionService, TenantService $tenantService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->tenantService = $tenantService;
    }

    public function index()
    {
        $tenant = $this->tenantService->getActiveTenant();
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()->route('subscription.plans');
        }

        $dashboard = $this->subscriptionService->getDashboardData($subscription);
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('subscription.index', compact('dashboard', 'plans'));
    }
    public function plans()
    {
        $tenant = $this->tenantService->getActiveTenant();
        $subscription = $tenant->subscription;
        $currentPlan = $subscription ? $subscription->plan : null;

        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('subscription.plans', compact('plans', 'currentPlan'));
    }

    public function subscribe(Request $request, Plan $plan)
    {
        $tenant = $this->tenantService->getActiveTenant();

        // Verificar se já tem subscrição
        if ($tenant->subscription) {
            return redirect()->route('subscription.index')->with('error', 'Já tem uma subscrição ativa.');
        }

        $subscription = $this->subscriptionService->createSubscription($tenant, $plan, [
            'trial_days' => $request->input('trial_days', 14),
        ]);

        return redirect()->route('subscription.index')->with('success', 'Subscrição iniciada com sucesso!');
    }

    public function upgrade(Request $request, Plan $plan)
    {
        $tenant = $this->tenantService->getActiveTenant();
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()->route('subscription.plans')->with('error', 'Não tem uma subscrição ativa.');
        }

        if ((int) $subscription->plan_id === (int) $plan->id) {
            return redirect()->back()->with('error', 'Já está neste plano.');
        }

        $oldPlan = $subscription->plan; // OBTÉM O PLANO ANTES DE ALTERAR
        $oldPlanId = $subscription->plan_id;

        // ATUALIZAR O PLANO
        $subscription->plan_id = $plan->id;
        $subscription->save();
        $subscription->refresh();

        // REGISTAR NO HISTÓRICO
        $subscription->logs()->create([
            'old_plan_id' => $oldPlanId,
            'new_plan_id' => $plan->id,
            'action' => $plan->price > $oldPlan->price ? 'upgrade' : 'downgrade',
            'notes' => "Plano alterado de {$oldPlan->name} para {$plan->name}",
        ]);

        return redirect()->route('subscription.index')->with('success', 'Plano alterado com sucesso!');
    }
    public function cancel()
    {
        $tenant = $this->tenantService->getActiveTenant();
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()->route('subscription.index');
        }

        $this->subscriptionService->cancelSubscription($subscription);

        return redirect()->route('subscription.index')->with('success', 'Subscrição cancelada.');
    }

    public function logs()
    {
        $tenant = $this->tenantService->getActiveTenant();
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()->route('subscription.index');
        }

        $logs = $subscription->logs()->with(['oldPlan', 'newPlan'])->orderBy('created_at', 'desc')->get();

        return view('subscription.logs', compact('subscription', 'logs'));
    }
}
