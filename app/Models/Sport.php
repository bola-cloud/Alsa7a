<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

use App\Traits\HasSlug;

class Sport extends Model
{
    use HasFactory, Translatable, HasSlug;

    protected $fillable = ['name', 'name_en', 'name_ar', 'description', 'description_en', 'description_ar', 'slug', 'icon_url', 'active'];

    // Ensure icon_url returns full absolute URL in API responses
    protected $appends = ['icon_url'];

    public function getIconUrlAttribute()
    {
        $val = $this->attributes['icon_url'] ?? null;
        if (!$val) {
            return null;
        }
        if (preg_match('#^https?://#i', $val)) {
            return $val;
        }
        // If it already starts with storage/, just return it (or verify asset() usage in view)
        // User wants the stored path to be returned. 
        // If we use asset() in view, we need the path relative to public root. 
        // We stored 'storage/sports/x.jpg'.
        return asset($val);
    }

    public function setIconUrlAttribute($value)
    {
        $this->attributes['icon_url'] = $value;
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_sport');
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function leagues()
    {
        return $this->hasMany(League::class);
    }
}
