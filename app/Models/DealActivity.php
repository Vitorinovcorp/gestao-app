<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealActivity extends Model
{
    protected $fillable = [
        'deal_id',
        'type',
        'description',
        'scheduled_at',
        'user_id'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute()
    {
        $map = [
            'call' => 'Chamada',
            'email' => 'Email',
            'meeting' => 'Reunião',
            'note' => 'Nota',
            'invoice' => 'Fatura',
            'update' => 'Atualização'
        ];
        return $map[$this->type] ?? ucfirst($this->type);
    }
}
