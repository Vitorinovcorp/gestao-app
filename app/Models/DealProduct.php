<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealProduct extends Model
{
    protected $fillable = [
        'deal_id', 'article_id', 'quantity', 'price'
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