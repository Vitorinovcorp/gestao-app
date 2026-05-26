<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $with = ['plan'];

    protected $fillable = [
        'tenant_id', 'plan_id', 'status', 'starts_at', 'ends_at',
        'trial_ends_at', 'cancelled_at', 'next_billing_at', 'auto_renew', 'meta'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'auto_renew' => 'boolean',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function usage(): HasMany
    {
        return $this->hasMany(SubscriptionUsage::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SubscriptionLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function getUsedLimit(string $feature): int
    {
        $usage = $this->usage()->where('feature', $feature)->first();
        return $usage ? $usage->used : 0;
    }

    public function getLimit(string $feature): ?int
    {
        return $this->plan->getLimit($feature);
    }

    public function hasReachedLimit(string $feature): bool
    {
        $limit = $this->getLimit($feature);
        if ($limit === null) {
            return false;
        }
        return $this->getUsedLimit($feature) >= $limit;
    }
}