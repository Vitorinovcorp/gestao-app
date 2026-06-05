<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRule extends Model
{
    protected $fillable = [
        'name', 'trigger_type', 'conditions', 'action_type', 
        'action_config', 'tenant_id', 'is_active'
    ];

    protected $casts = [
        'conditions' => 'array',
        'action_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}