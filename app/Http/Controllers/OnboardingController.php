<?php

namespace App\Http\Controllers;

use App\Services\OnboardingService;
use App\Services\TenantService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    protected OnboardingService $onboardingService;
    protected TenantService $tenantService;

    public function __construct(OnboardingService $onboardingService, TenantService $tenantService)
    {
        $this->onboardingService = $onboardingService;
        $this->tenantService = $tenantService;
    }

    public function index()
    {
        $tenant = $this->tenantService->getActiveTenant();

        if (!$tenant) {
            return redirect()->route('tenants.create');
        }

        $progress = $this->onboardingService->getProgress($tenant);

        return view('onboarding.index', compact('tenant', 'progress'));
    }

    public function step(Request $request, $step = null)
    {
        $tenant = $this->tenantService->getActiveTenant();

        if (!$tenant) {
            return redirect()->route('tenants.create');
        }

        if ($step === null) {
            $step = 1;
        }

        if ($step > 7) {
            return redirect()->route('onboarding.completed');
        }

        $taskNames = [
            1 => 'branding',
            2 => 'users',
            3 => 'permissions',
            4 => 'company',
            5 => 'first_client',
            6 => 'first_article',
            7 => 'first_proposal'
        ];

        $taskKey = $taskNames[$step] ?? null;

        if (!$taskKey) {
            return redirect()->route('onboarding.completed');
        }

        $clients = collect();
        if ($taskKey === 'first_proposal') {
            $clients = \App\Models\Entity::where('tenant_id', $tenant->id)
                ->where('type', 'client')
                ->orderBy('name')
                ->get();

            if ($clients->isEmpty()) {
                return redirect()->route('onboarding.step', ['step' => 5])
                    ->with('error', 'Crie um cliente primeiro antes de criar uma proposta.');
            }
        }

        return view('onboarding.step', compact('tenant', 'step', 'taskKey', 'clients'));
    }

    public function process(Request $request, $step)
    {
        $tenant = $this->tenantService->getActiveTenant();

        if (!$tenant) {
            return redirect()->route('tenants.create');
        }

        $taskKeys = [
            1 => 'branding',
            2 => 'users',
            3 => 'permissions',
            4 => 'company',
            5 => 'first_client',
            6 => 'first_article',
            7 => 'first_proposal'
        ];

        $taskKey = $taskKeys[$step] ?? null;

        if (!$taskKey) {
            return redirect()->route('onboarding.completed');
        }

        switch ($taskKey) {
            case 'branding':
                if ($request->has('company_name')) {
                    $tenant->update(['name' => $request->input('company_name')]);
                }
                if ($request->has('primary_color')) {
                    $tenant->update(['primary_color' => $request->input('primary_color')]);
                }
                if ($request->hasFile('logo')) {
                    $path = $request->file('logo')->store('logos', 'public');
                    $tenant->update(['logo' => $path]);
                }
                break;

            case 'users':
                if ($request->has('invite_email')) {
                }
                break;

            case 'permissions':
                break;

            case 'company':
                $companyData = $request->only(['nif', 'address', 'postal_code', 'city', 'country']);
                $settings = $tenant->settings ?? [];
                $settings['company'] = $companyData;
                $tenant->update(['settings' => $settings]);
                break;

            case 'first_client':
                if ($request->has('client_name')) {
                    $client = new \App\Models\Entity();
                    $client->tenant_id = $tenant->id;
                    $client->name = $request->input('client_name');
                    $client->type = 'client';
                    $client->nif = $request->input('client_nif');
                    $client->email = $request->input('client_email');
                    $client->phone = $request->input('client_phone');
                    $client->number = 'CLI-' . date('YmdHis');
                    $client->save();

                    $task = \App\Models\OnboardingTask::where('tenant_id', $tenant->id)
                        ->where('task_key', 'first_client')
                        ->first();

                    if ($task) {
                        $task->update(['is_completed' => true]);
                    }
                }
                break;

            case 'first_article':
                if ($request->has('article_name')) {
                    $vatRate = \App\Models\VatRate::where('rate', 23)->first();
                    $vatId = $vatRate ? $vatRate->id : null;

                    if (!$vatId) {
                        $vatRate = \App\Models\VatRate::create([
                            'rate' => 23,
                            'name' => 'IVA 23%',
                            'is_active' => true,
                        ]);
                        $vatId = $vatRate->id;
                    }

                    \App\Models\Article::create([
                        'tenant_id' => $tenant->id,
                        'name' => $request->input('article_name'),
                        'reference' => $request->input('article_reference'),
                        'price' => $request->input('article_price', 0),
                        'vat_id' => $vatId,
                        'status' => 'active',
                    ]);
                }
                break;

            case 'first_proposal':
                if ($request->has('proposal_client')) {
                    $clientId = $request->input('proposal_client');
                    $client = \App\Models\Entity::find($clientId);

                    if ($client) {
                        $number = 'PROP-' . date('YmdHis');

                        \App\Models\Proposal::create([
                            'tenant_id' => $tenant->id,
                            'client_id' => $client->id,
                            'title' => $request->input('proposal_title', 'Proposta Comercial'),
                            'amount' => $request->input('proposal_amount', 0),
                            'description' => $request->input('proposal_description', ''),
                            'number' => $number,
                            'proposal_date' => now()->toDateString(),
                            'validity' => now()->addDays(30)->toDateString(),
                            'status' => 'draft',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
                break;
        }

        $task = \App\Models\OnboardingTask::where('tenant_id', $tenant->id)
            ->where('task_key', $taskKey)
            ->first();

        if ($task) {
            $task->update(['is_completed' => true]);
            \Log::info('Tarefa marcada como concluída: ' . $taskKey);
        } else {
            \Log::error('Tarefa não encontrada: ' . $taskKey);
        }

        $nextStep = $step + 1;

        return redirect()->route('onboarding.step', ['step' => $nextStep]);
    }

    public function completed()
    {
        $tenant = $this->tenantService->getActiveTenant();

        if (!$tenant) {
            return redirect()->route('tenants.create');
        }

        return view('onboarding.completed', compact('tenant'));
    }

    public function apiProgress()
    {
        $tenant = $this->tenantService->getActiveTenant();

        if (!$tenant) {
            return response()->json(['error' => 'No active tenant'], 404);
        }

        return response()->json($this->onboardingService->getProgress($tenant));
    }
}
