<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A question the publisher attaches to their request, answered by applicants
 * while applying (e.g. "ما هو مركزك؟" with a list of positions).
 */
class MarketRequestQuestion extends Model
{
    protected $fillable = [
        'market_request_id',
        'question',
        'is_required',
        'options',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
        'sort_order' => 'integer',
    ];

    public function marketRequest()
    {
        return $this->belongsTo(MarketRequest::class);
    }

    public function answers()
    {
        return $this->hasMany(MarketApplicationAnswer::class);
    }

    /**
     * Every question is multiple choice — the answer must be one of these.
     *
     * @return array<int, string>
     */
    public function choices()
    {
        return (array) ($this->options ?? []);
    }
}
