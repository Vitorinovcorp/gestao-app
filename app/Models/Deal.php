<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    protected $fillable = [
        'entity_id',
        'person_id',
        'title',
        'value',
        'stage',
        'probability',
        'expected_close_date',
        'owner_id',
        'tenant_id'
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(DealActivity::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(DealProduct::class);
    }

    public function activateFollowUp()
    {
        $this->follow_up_active = true;
        $this->follow_up_started_at = now();
        $this->follow_up_email_index = 0;
        $this->follow_up_next_send_at = $this->getNextBusinessDate(now()->addHours(2));
        $this->save();
    }

    public function deactivateFollowUp()
    {
        $this->follow_up_active = false;
        $this->follow_up_cancelled_at = now();
        $this->save();
    }

    public function getNextBusinessDate($date)
    {
        $date = \Carbon\Carbon::parse($date);

        while ($date->isWeekend()) {
            $date->addDay();
        }

        $hour = (int) $date->format('H');
        if ($hour < 9) {
            $date->hour = 9;
        } elseif ($hour >= 18) {
            $date->addDay();
            $date->hour = 9;
        }
        $date->minute = 0;

        return $date;
    }

    public function followUpEmails()
    {
        return $this->hasMany(FollowUpEmail::class);
    }
}
