<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One applicant's answer to one of the request's questions.
 */
class MarketApplicationAnswer extends Model
{
    protected $fillable = [
        'market_application_id',
        'market_request_question_id',
        'answer',
    ];

    public function application()
    {
        return $this->belongsTo(MarketApplication::class, 'market_application_id');
    }

    public function question()
    {
        return $this->belongsTo(MarketRequestQuestion::class, 'market_request_question_id');
    }
}
