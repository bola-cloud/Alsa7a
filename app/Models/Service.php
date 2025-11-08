<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['provider_id', 'club_id', 'sport_id', 'title', 'slug', 'description', 'price', 'duration_minutes', 'currency', 'is_active', 'meta'];

    protected $casts = ['meta' => 'array', 'is_active' => 'boolean'];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function media()
    {
        return $this->hasMany(ServiceMedia::class);
    }

    public function requests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function reviews()
    {
        return $this->hasMany(ServiceReview::class);
    }
}
