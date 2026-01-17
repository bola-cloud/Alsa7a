<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class League extends Model
{
    use HasFactory, Translatable;

    protected $fillable = ['sport_id', 'name', 'name_en', 'name_ar', 'slug', 'description', 'description_en', 'description_ar', 'season', 'start_date', 'end_date', 'is_active', 'meta', 'image'];

    protected $casts = ['meta' => 'array', 'is_active' => 'boolean'];

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_league');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'league_team');
    }

    public function videos()
    {
        return $this->hasMany(LeagueVideo::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    /**
     * Return `image` as full URL for API consumers.
     */
    public function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }
        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }
        return asset($value);
    }

    /**
     * Mutator: store relative path when possible, keep external URLs as-is
     */
    public function setImageAttribute($value)
    {
        $this->attributes['image'] = $value;
    }
}
