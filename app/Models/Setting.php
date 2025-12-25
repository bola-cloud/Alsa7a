<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'group', 'label'];

    // Accessor for image URL
    public function getImageUrlAttribute()
    {
        if ($this->type === 'image' && $this->value) {
            return asset('storage/' . $this->value);
        }
        return null;
    }
}
