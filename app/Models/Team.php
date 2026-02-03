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

    /**
     * Return `image` as full URL.
     */
    public function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }
        // If it's already a full URL, return as-is
        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }
        // The ImageService stores paths as 'storage/teams/xyz.png'
        // We need to convert this to a full URL using asset()
        return asset($value);
    }

    /**
     * Mutator: store the path as-is from ImageService
     */
    public function setImageAttribute($value)
    {
        $this->attributes['image'] = $value;
    }
}
