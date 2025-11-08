<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    use HasFactory;

    protected $fillable = ['sport_id', 'name', 'slug', 'description', 'season', 'start_date', 'end_date', 'is_active', 'meta'];

    protected $casts = ['meta' => 'array', 'is_active' => 'boolean'];

    public function sport()
    {
        return $this->belongsTo(Sport::class);
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
}
