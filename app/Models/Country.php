<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'flag',
        'is_active',
        'subscription_monthly_price',
        'subscription_annual_price',
        'currency',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_monthly_price' => 'decimal:3',
        'subscription_annual_price' => 'decimal:3',
    ];

    /**
     * Display order for every list of countries.
     *
     * "Other" (code ZZ) is a catch-all rather than a market, so it belongs at
     * the bottom of the picker no matter how it sorts alphabetically. Every
     * caller goes through this scope so the API, the admin panel and the
     * broadcast form all agree on the order.
     */
    public function scopeOrdered($query)
    {
        return $query
            ->orderByRaw("CASE WHEN code = 'ZZ' THEN 1 ELSE 0 END")
            ->orderBy('name_en');
    }

    /**
     * Additive field for the mobile app: `flag` stays exactly as stored (a
     * relative path), `flag_url` is the same image as an absolute URL.
     *
     * @var array<int, string>
     */
    protected $appends = ['flag_url'];

    /**
     * @return string|null
     */
    public function getFlagUrlAttribute()
    {
        $flag = $this->attributes['flag'] ?? null;

        if (! $flag) {
            return null;
        }

        if (str_starts_with($flag, 'http://') || str_starts_with($flag, 'https://')) {
            return $flag;
        }

        return url(ltrim($flag, '/'));
    }
}
