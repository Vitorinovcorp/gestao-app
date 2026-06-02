<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'deal_id', 'invoice_number', 'issue_date', 'due_date', 'amount', 'status', 'tenant_id'
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}