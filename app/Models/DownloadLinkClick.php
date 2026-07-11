<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadLinkClick extends Model
{
    protected $fillable = [
        'link_type',
        'ip_address',
        'country',
        'os_type',
        'user_agent',
    ];
}
