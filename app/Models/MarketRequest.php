<?php

namespace App\Models;

use App\Traits\HasCountryScope;
use Illuminate\Database\Eloquent\Model;

class MarketRequest extends Model
{
    use HasCountryScope;

    protected $fillable = [
        'user_id',
        'club_id',
        'category_id',
        'country_id',
        'title',
        'description',
        'latitude',
        'longitude',
        'address',
        'scheduled_at',
        'cost',
        'status',
    ];

    protected $casts = [
        // Real numbers in the API, not the strings MySQL returns for DECIMAL.
        'latitude' => 'float',
        'longitude' => 'float',
        'cost' => 'float',
        // Wall-clock, not an instant — see UserEvent::$casts.
        'scheduled_at' => 'datetime:Y-m-d\TH:i:s',
    ];

    /**
     * The user who posted this job. Any user whose category has
     * `is_marketplace` enabled can post — not only club owners.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Optional: only set if the poster happened to own a club at posting
     * time. Purely informational — never required, never the authorization
     * check (see MarketController).
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function applications()
    {
        return $this->hasMany(MarketApplication::class);
    }

    /**
     * Questions the publisher wants every applicant to answer.
     */
    public function questions()
    {
        return $this->hasMany(MarketRequestQuestion::class)->orderBy('sort_order');
    }
}
