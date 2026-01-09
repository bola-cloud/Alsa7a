<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class Slider extends Model
{
    use HasFactory, Translatable;

    protected $table = 'sliders';

    protected $fillable = ['title', 'title_en', 'title_ar', 'image'];

    // Append computed full image URL
    protected $appends = ['image_url'];

    // Hide the raw stored image path from API responses; clients should use `image_url`.
    protected $hidden = ['image'];

    /**
     * Accessor for full image URL
     */
    public function getImageUrlAttribute()
    {
        $val = $this->attributes['image'] ?? null;
        if (!$val) {
            return null;
        }
        if (preg_match('#^https?://#i', $val)) {
            return $val;
        }
        return asset($val);
    }

    public function setImageAttribute($value)
    {
        $this->attributes['image'] = $value;
    }
}
