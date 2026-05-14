<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'article_id',
        'supplier_id',
        'quantity',
        'quantity_delivered',
        'unit_price',
        'cost_price',
        'discount_percent',
        'discount_value',
        'vat_rate',
        'line_subtotal',
        'line_vat',
        'line_total',
        'notes',
        'sort_order'
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
    
    public function supplier()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }
}