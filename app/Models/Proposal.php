<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proposal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number',
        'proposal_date',
        'client_id',
        'validity',
        'status',
        'total_value',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'proposal_date' => 'date',
        'validity' => 'date'
    ];

    public function client()
    {
        return $this->belongsTo(Entity::class, 'client_id');
    }

    public function lines()
    {
        return $this->hasMany(ProposalLine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}