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
}
