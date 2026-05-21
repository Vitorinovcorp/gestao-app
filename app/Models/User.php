<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'telefone',
        'grupo_permissoes',
        'status',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
    ];

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function setIsActiveAttribute(bool $value): void
    {
        $this->status = $value ? 'active' : 'inactive';
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
                    ->withPivot('role', 'permissions')
                    ->withTimestamps();
    }

    public function ownsTenant(Tenant $tenant): bool
    {
        return $this->id === $tenant->owner_id;
    }

    public function activeTenant(): ?Tenant
    {
        return $this->tenants()->first();
    }

    public function ownedTenants()
    {
        return $this->tenants()->wherePivot('role', 'owner');
    }

    public function hasTenantAccess(Tenant $tenant): bool
    {
        return $this->tenants()->where('tenant_id', $tenant->id)->exists();
    }

    public function getTenantRole(Tenant $tenant): ?string
    {
        $pivot = $this->tenants()->where('tenant_id', $tenant->id)->first();
        return $pivot ? $pivot->pivot->role : null;
    }


    protected static function booted()
    {
        static::created(function ($user) {
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'user_data' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'telefone' => $user->telefone,
                        'status' => $user->status,
                    ],
                    'menu' => 'Utilizadores'
                ])
                ->log("Novo utilizador criado: {$user->name}");
        });

        static::updated(function ($user) {
            $changes = [];
            foreach ($user->getChanges() as $field => $newValue) {
                if (!in_array($field, ['updated_at'])) {
                    $changes[$field] = [
                        'old' => $user->getOriginal($field),
                        'new' => $newValue
                    ];
                }
            }

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'changes' => $changes,
                    'menu' => 'Utilizadores'
                ])
                ->log("Utilizador atualizado: {$user->name}");
        });

        static::deleted(function ($user) {
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'deleted_user_data' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'telefone' => $user->telefone,
                    ],
                    'menu' => 'Utilizadores'
                ])
                ->log("Utilizador eliminado: {$user->name}");
        });
    }
}