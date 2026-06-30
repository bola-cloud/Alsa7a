<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'sender_id', 'body', 'read_at', 'meta', 'file_path'];

    protected $casts = [
        'read_at' => 'datetime',
        'meta'    => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return asset($this->file_path);
        }
        return null;
    }
}
