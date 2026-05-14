<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'article_id',
        'quantity',
        'unit_price'
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}