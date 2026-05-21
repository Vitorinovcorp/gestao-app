<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\TenantService;
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

        if (!$tenant && $user->tenants()->exists()) {
            $firstTenant = $user->tenants()->first();
            $this->tenantService->setActiveTenant($firstTenant);
            $tenant = $firstTenant;
        }

        if (!$tenant) {
            return redirect()->route('tenants.create');
        }

        if (!$this->tenantService->userHasAccess($user, $tenant)) {
            abort(403, 'Acesso não autorizado a este tenant');
        }

        $request->merge(['active_tenant' => $tenant]);
        
        return $next($request);
    }
}