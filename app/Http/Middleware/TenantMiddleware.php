<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\TenantService;
use App\Services\OnboardingService;
use Illuminate\Http\Request;

class TenantMiddleware
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $tenant = $this->tenantService->getActiveTenant();

        // Se não tiver tenant ativo, tenta pegar o primeiro
        if (!$tenant && $user->tenants()->exists()) {
            $firstTenant = $user->tenants()->first();
            $this->tenantService->setActiveTenant($firstTenant);
            $tenant = $firstTenant;
        }

        // Se ainda não tiver tenant, redireciona para criar um
        if (!$tenant) {
            return redirect()->route('tenants.create');
        }

        // Verificar se o utilizador tem acesso ao tenant
        if (!$this->tenantService->userHasAccess($user, $tenant)) {
            abort(403, 'Acesso não autorizado a este tenant');
        }

        // Verificar status do onboarding
        $onboardingService = app(OnboardingService::class);
        $status = $onboardingService->getStatus($tenant);
        
        // Se o onboarding NÃO estiver completo e NÃO estiver na rota de onboarding, redirecionar
        if ($status !== 'completed' && !$request->routeIs('onboarding.*')) {
            return redirect()->route('onboarding.index');
        }

        $request->merge(['active_tenant' => $tenant]);
        
        return $next($request);
    }
}