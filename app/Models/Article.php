<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'name',
        'description',
        'price',
        'cost_price',
        'vat_id',
        'photo_path',
        'barcode',
        'stock_min',
        'stock_current',
        'observations',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'cost_price' => 'decimal:4',
        'is_active' => 'boolean'
    ];

    public function vat()
    {
        return $this->belongsTo(VatRate::class, 'vat_id');
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }
        return null;
    }
}