<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileVisit extends Model
{
    protected $fillable = ['visitor_id', 'visited_id'];

    public function visitor()
    {
        return $this->belongsTo(User::class, 'visitor_id');
    }

    public function visited()
    {
        return $this->belongsTo(User::class, 'visited_id');
    }
}
