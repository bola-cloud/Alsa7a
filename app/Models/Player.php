<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = ['team_id', 'club_id', 'name', 'slug', 'position', 'number', 'nationality', 'profile_photo', 'stats', 'is_featured'];

    protected $casts = ['stats' => 'array', 'is_featured' => 'boolean'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }
}
