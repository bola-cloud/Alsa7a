<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeagueVideo extends Model
{
    use HasFactory;

    protected $table = 'league_videos';

    protected $fillable = ['league_id', 'title', 'url', 'duration_seconds', 'meta'];

    protected $casts = ['meta' => 'array'];

    public function league()
    {
        return $this->belongsTo(League::class);
    }
}
