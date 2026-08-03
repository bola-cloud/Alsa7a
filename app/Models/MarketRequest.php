<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketRequest extends Model
{
    protected $fillable = [
        'club_id',
        'category_id',
        'country_id',
        'title',
        'description',
        'status',
    ];

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
}
