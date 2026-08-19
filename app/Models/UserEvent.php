<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEvent extends Model
{
    protected $fillable = [
        'user_id',
        'country_id',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'address',
        'event_date',
    ];

    protected $casts = [
        // Wall-clock, not an instant: no zone marker, so no client shifts it.
        'event_date' => 'datetime:Y-m-d\TH:i:s',
        // Cast so the API returns real numbers, not the "23.5880000" strings
        // MySQL hands back for DECIMAL columns.
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
