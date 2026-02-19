<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRating extends Model
{
    use HasFactory;

    protected $fillable = ['reviewer_id', 'rated_id', 'rating', 'comment'];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function rated()
    {
        return $this->belongsTo(User::class, 'rated_id');
    }
}
