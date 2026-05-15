<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_order_id',
        'article_id',
        'order_line_id',
        'quantity',
        'quantity_received',
        'unit_price',
        'discount_percent',
        'discount_value',
        'vat_rate',
        'line_subtotal',
        'line_vat',
        'line_total',
        'notes',
        'sort_order'
    ];

    public function supplierOrder()
    {
        return $this->belongsTo(SupplierOrder::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}