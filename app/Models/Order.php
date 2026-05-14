<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number',
        'order_date',
        'client_id',
        'status',
        'total_value',
        'notes',
        'created_by',
        'confirmed_at',
        'expected_delivery'
    ];

    protected $casts = [
        'order_date' => 'date',
        'confirmed_at' => 'datetime',
        'expected_delivery' => 'date'
    ];

    public function client()
    {
        return $this->belongsTo(Entity::class, 'client_id');
    }

    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}