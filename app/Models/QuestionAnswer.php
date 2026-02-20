<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model
{
    use HasFactory;

    protected $table = 'question_answers';

    protected $fillable = ['user_id', 'question_id', 'answer', 'is_visible'];

    protected $casts = [
        'answer' => 'array',
        'is_visible' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
