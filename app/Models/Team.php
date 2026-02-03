<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasSlug;

class Team extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['club_id', 'sport_id', 'name', 'short_name', 'slug', 'jersey_color', 'coach', 'founded_year', 'active', 'meta', 'image', 'age_group'];

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

    /**
     * Users who are members of this team.
     */
    public function members()
    {
        return $this->hasMany(User::class);
    }

    // Legacy or separate entity relationship if needed, keeping for backward compatibility
    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }
}
