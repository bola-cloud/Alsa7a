<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMedia extends Model
{
    use HasFactory;

    protected $table = 'service_media';

    protected $fillable = ['service_id', 'url', 'type', 'title', 'meta'];

    protected $casts = ['meta' => 'array'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
