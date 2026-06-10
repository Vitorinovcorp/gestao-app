<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AISuggestion extends Model
{
    protected $table = 'ai_suggestions'; 

    protected $fillable = [
        'deal_id', 'user_id', 'type', 'title', 'description', 'reason',
        'status', 'suggested_at', 'accepted_at', 'dismissed_at', 'metadata'
    ];

    protected $casts = [
        'suggested_at' => 'datetime',
        'accepted_at' => 'datetime',
        'dismissed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}