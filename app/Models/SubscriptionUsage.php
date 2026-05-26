<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionUsage extends Model
{

    protected $table = 'subscription_usage';

    protected $fillable = [
        'subscription_id',
        'feature',
        'used',
        'limit',
        'reset_at'
    ];

    protected $casts = [
        'reset_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function incrementUsage(int $amount = 1): void
    {
        $this->used += $amount;
        $this->save();
    }

    public function decrementUsage(int $amount = 1): void
    {
        $this->used -= $amount;
        $this->save();
    }
}
