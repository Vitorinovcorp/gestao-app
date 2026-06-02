<?php

use App\Models\Tenant;
use App\Services\TenantService;

if (!function_exists('tenant')) {
    function tenant(): ?Tenant
    {
        return app(TenantService::class)->getActiveTenant();
    }
}

if (!function_exists('tenant_id')) {
    function tenant_id(): ?int
    {
        return app(TenantService::class)->getActiveTenantId();
    }
}