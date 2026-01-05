<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class CommunityCategory extends Model
{
    use HasFactory, Translatable;

    protected $fillable = ['name_en', 'name_ar', 'image'];
}
