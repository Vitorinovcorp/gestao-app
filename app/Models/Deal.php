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

    public function activateFollowUp()
    {
        $this->update([
            'follow_up_active' => true,
            'follow_up_started_at' => now(),
            'follow_up_next_send_at' => now()->addDay(),
            'follow_up_email_index' => 0,
        ]);
    }

    public function deactivateFollowUp()
    {
        $this->update([
            'follow_up_active' => false,
            'follow_up_cancelled_at' => now(),
        ]);
    }

    public function products(): HasMany
    {
        return $this->hasMany(DealProduct::class);
    }
}
