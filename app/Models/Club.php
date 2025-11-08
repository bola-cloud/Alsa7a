<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'logo_url', 'banner_url', 'city', 'country', 'founded_year', 'website', 'rating', 'is_featured', 'meta'];

    protected $casts = [
        'meta' => 'array',
        'is_featured' => 'boolean',
    ];

    public function sports()
    {
        return $this->belongsToMany(Sport::class, 'club_sport');
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }
}
