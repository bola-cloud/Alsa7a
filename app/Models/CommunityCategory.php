<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class CommunityCategory extends Model
{
    use HasFactory, Translatable;

    protected $fillable = ['name_en', 'name_ar', 'image'];

    // Append full URL for image
    protected $appends = ['image'];

    public function getImageAttribute()
    {
        $val = $this->attributes['image'] ?? null;
        if (!$val) {
            return null;
        }
        if (preg_match('#^https?://#i', $val)) {
            return $val;
        }
        return asset('storage/' . $val);
    }
}
