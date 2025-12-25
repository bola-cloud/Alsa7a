<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'ticket_number',
        'ticket_type',
        'name',
        'email',
        'phone',
        'seats',
        'price_paid',
        'status',
        'payment_meta'
    ];

    protected $casts = ['payment_meta' => 'array'];

    const TICKET_TYPE_REGULAR = 'regular';
    const TICKET_TYPE_VIP = 'vip';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
