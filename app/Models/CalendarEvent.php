<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'duration_minutes',
        'type_id',
        'action_id',
        'entity_id',
        'user_id',
        'assigned_to',
        'status',
        'location',
        'meeting_link',
        'is_all_day',
        'requires_confirmation',
        'is_confirmed',
        'share',
        'knowledge',
        'reminders',
        'attendees'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_all_day' => 'boolean',
        'requires_confirmation' => 'boolean',
        'is_confirmed' => 'boolean',
        'reminders' => 'array',
        'attendees' => 'array'
    ];

    public function type()
    {
        return $this->belongsTo(CalendarType::class, 'type_id');
    }

    public function action()
    {
        return $this->belongsTo(CalendarAction::class, 'action_id');
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}