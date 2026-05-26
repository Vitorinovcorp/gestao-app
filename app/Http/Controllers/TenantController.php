<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantService;
use App\Services\OnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index()
    {
        $tenants = $this->tenantService->getUserTenants(auth()->user());
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Criar o tenant e inicializar o onboarding
        $tenant = $this->tenantService->createTenant(auth()->user(), $request->all());

        // Verificar se o onboarding foi inicializado
        $onboardingService = app(OnboardingService::class);
        $status = $onboardingService->getStatus($tenant);

        // Se o status for 'not_started', inicializar manualmente
        if ($status === 'not_started') {
            $onboardingService->initializeOnboarding($tenant);
        }

        // FORÇAR REDIRECIONAMENTO PARA ONBOARDING
        return redirect()->route('onboarding.step', ['step' => 1])->with('success', 'Tenant criado! Vamos configurar o seu ambiente.');
    }

    public function switch(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $user = auth()->user();

        if ($this->tenantService->switchTenant($tenant, $user)) {
            return redirect()->back()->with('success', 'Tenant alterado para: ' . $tenant->name);
        }

        return redirect()->back()->with('error', 'Não tem permissão para aceder a este tenant');
    }

    public function settings()
    {
        $tenant = $this->tenantService->getActiveTenant();
        return view('tenants.settings', compact('tenant'));
    }

    public function updateSettings(Request $request)
    {
        $tenant = $this->tenantService->getActiveTenant();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tenant->update($request->only(['name', 'is_active']));

        return redirect()->back()->with('success', 'Configurações do tenant atualizadas!');
    }

    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);

        // Verificar permissões (apenas owner pode remover)
        if ($tenant->owner_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Apenas o proprietário pode remover o tenant.');
        }

        $tenant->delete();

        return redirect()->route('tenants.index')->with('success', 'Tenant removido com sucesso.');
    }
}
