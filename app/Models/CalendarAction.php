<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'requires_followup',
        'default_duration',
        'is_active'
    ];

    protected $casts = [
        'requires_followup' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function events()
    {
        return $this->hasMany(CalendarEvent::class, 'action_id');
    }
}