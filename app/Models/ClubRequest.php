<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'club_id',
        'type', // 'join', 'invite'
        'status', // 'pending', 'accepted', 'rejected'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
