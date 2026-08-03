<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketApplication extends Model
{
    protected $fillable = [
        'market_request_id',
        'user_id',
        'cv_path',
        'notes',
    ];

    public function marketRequest()
    {
        return $this->belongsTo(MarketRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
