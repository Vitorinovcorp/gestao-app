<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'invoice_date',
        'due_date',
        'supplier_id',
        'supplier_order_id',
        'total_value',
        'document_path',
        'payment_proof_path',
        'status',
        'observations',
        'paid_at',
        'paid_by',
        'created_by'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'total_value' => 'decimal:2'
    ];

    public function supplier()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }

    public function supplierOrder()
    {
        return $this->belongsTo(SupplierOrder::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}