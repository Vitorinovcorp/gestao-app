<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealLine extends Model
{
    protected $fillable = [
        'deal_id',
        'article_id',
        'quantity',
        'unit_price',
        'total_price',
        'tenant_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}