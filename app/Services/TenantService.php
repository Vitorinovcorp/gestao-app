<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TenantService
{
    const SESSION_KEY = 'active_tenant';

    public function setActiveTenant(Tenant $tenant): void
    {
        Session::put(self::SESSION_KEY, $tenant->id);
        Session::put('tenant_name', $tenant->name);
        Session::put('tenant_slug', $tenant->slug);
    }

    public function getActiveTenant(): ?Tenant
    {
        $tenantId = Session::get(self::SESSION_KEY);
        
        if ($tenantId) {
            return Tenant::with('users')->find($tenantId);
        }
        
        $user = auth()->user();
        if ($user) {
            $firstTenant = $user->tenants()->first();
            if ($firstTenant) {
                $this->setActiveTenant($firstTenant);
                return $firstTenant;
            }
        }
        
        return null;
    }

    public function getActiveTenantId(): ?int
    {
        return Session::get(self::SESSION_KEY);
    }

    public function getUserTenants(User $user)
    {
        return $user->tenants()->with('owner')->get();
    }

    public function createTenant(User $user, array $data): Tenant
    {
        return DB::transaction(function () use ($user, $data) {
            $slug = Str::slug($data['name']);
            $originalSlug = $slug;
            $counter = 1;
            
            while (Tenant::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            
            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $slug,
                'domain' => $data['domain'] ?? null,
                'logo' => $data['logo'] ?? null,
                'primary_color' => $data['primary_color'] ?? config('tenant.default_color', '#6D5BD0'),
                'settings' => $data['settings'] ?? [],
                'is_active' => true,
                'owner_id' => $user->id,
            ]);
            
            $tenant->users()->attach($user->id, [
                'role' => 'owner',
                'permissions' => null,
            ]);
            
            app(OnboardingService::class)->initializeOnboarding($tenant);
            
            $this->setActiveTenant($tenant);
            
            return $tenant;
        });
    }

    public function userHasAccess(User $user, Tenant $tenant): bool
    {
        return $tenant->hasUser($user);
    }

    public function switchTenant(Tenant $tenant, User $user): bool
    {
        if (!$this->userHasAccess($user, $tenant)) {
            return false;
        }
        
        $this->setActiveTenant($tenant);
        return true;
    }

    public function clearActiveTenant(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::forget('tenant_name');
        Session::forget('tenant_slug');
    }
}