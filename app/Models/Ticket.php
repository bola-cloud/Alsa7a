<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'status', // open, in_progress, resolved, closed
        'priority',
        'ticketable_id',
        'ticketable_type',
        'admin_notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticketable()
    {
        return $this->morphTo();
    }
}
