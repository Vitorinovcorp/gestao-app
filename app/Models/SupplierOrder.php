<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number',
        'order_date',
        'supplier_id',
        'customer_order_id',
        'status',
        'total_value',
        'delivery_address',
        'notes',
        'internal_notes',
        'created_by',
        'updated_by',
        'tracking_code',
        'expected_delivery',
        'ordered_at',
        'received_at'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery' => 'date',
        'ordered_at' => 'datetime',
        'received_at' => 'datetime'
    ];

    public function supplier()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }

    public function lines()
    {
        return $this->hasMany(SupplierOrderLine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}