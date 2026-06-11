<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'trigger_type',
        'inactivity_days',
        'conditions',
        'action_type',
        'activity_type',
        'activity_priority',
        'send_notification',
        'is_active',
        'tenant_id',
    ];

    protected $casts = [
        'conditions' => 'array',
        'send_notification' => 'boolean',
        'is_active' => 'boolean',
        'inactivity_days' => 'integer',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(AutomationLog::class);
    }

    public function getPriorityColorAttribute()
    {
        return match($this->activity_priority) {
            'high' => 'text-red-600 bg-red-100',
            'medium' => 'text-yellow-600 bg-yellow-100',
            'low' => 'text-green-600 bg-green-100',
            default => 'text-gray-600 bg-gray-100',
        };
    }
}