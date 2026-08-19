<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'requester_id',
        'provider_id',
        'status',
        'is_disputed',
        'message',
        'scheduled_at',
        'end_at',
        'price',
        'payment_status',
        'payment_transaction_id',
        'payment_meta',
        'is_free',
    ];

    protected $casts = [
        'payment_meta' => 'array',
        // Wall-clock, not instants — see UserEvent::$casts. The customer
        // picks the hour the service happens at; nothing may shift it.
        'scheduled_at' => 'datetime:Y-m-d\TH:i:s',
        'end_at'       => 'datetime:Y-m-d\TH:i:s',
        'is_disputed'  => 'boolean',
        'is_free'      => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }
}
