<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingTask extends Model
{
    protected $fillable = [
        'tenant_id', 'task_key', 'title', 'description',
        'is_completed', 'order', 'data'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'data' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}