<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'entity_id',
        'number',
        'first_name',
        'last_name',
        'phone',
        'mobile',
        'email',
        'observations',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
}
