<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['club_id', 'sport_id', 'name', 'short_name', 'slug', 'jersey_color', 'coach', 'founded_year', 'active', 'meta'];

    protected $casts = ['meta' => 'array', 'active' => 'boolean'];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function leagues()
    {
        return $this->belongsToMany(League::class, 'league_team');
    }

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }
}
