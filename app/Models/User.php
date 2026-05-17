<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
// Removido: use Spatie\Activitylog\Traits\LogsActivity;
// Removido: use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles; // Removeu LogsActivity

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
    ];

    /**
     * Acessor para o atributo is_active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Mutator para o atributo is_active
     */
    public function setIsActiveAttribute(bool $value): void
    {
        $this->status = $value ? 'active' : 'inactive';
    }
    
    /**
     * Boot do modelo - registrar ações manuais
     */
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