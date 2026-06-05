<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicForm extends Model
{
    protected $fillable = [
        'title', 'slug', 'fields', 'embed_code', 'confirmation_message',
        'is_active', 'success_url', 'tenant_id'
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getEmbedCodeAttribute()
    {
        return $this->attributes['embed_code'] ?? $this->generateEmbedCode();
    }

    protected function generateEmbedCode()
    {
        return 'form_' . str_random(20);
    }
}