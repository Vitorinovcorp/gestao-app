<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo',
        'primary_color',
        'settings',
        'is_active',
        'owner_id'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot('role', 'permissions')
            ->withTimestamps();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }


    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->latestOfMany();
    }


    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function userRole(User $user): ?string
    {
        $pivot = $this->users()
            ->where('user_id', $user->id)
            ->first();

        return $pivot ? $pivot->pivot->role : null;
    }

    public function hasUser(User $user): bool
    {
        return $this->users()
            ->where('user_id', $user->id)
            ->exists();
    }
}