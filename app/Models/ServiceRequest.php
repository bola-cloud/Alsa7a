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
        'payment_meta'
    ];

    protected $casts = [
        'payment_meta' => 'array',
        'scheduled_at' => 'datetime',
        'end_at' => 'datetime',
        'is_disputed' => 'boolean'
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
}
