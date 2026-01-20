<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'question',
        'meta',
        'type', //text, multiple_choice, boolean, number only
        'choices',
    ];

    protected $casts = [
        'meta' => 'array',
        'choices' => 'array',
        'question' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get translation for a specific key (simulates Translatable trait behavior for JSON column)
     */
    public function getTranslation($key, $locale)
    {
        if ($key === 'question') {
            return $this->question[$locale] ?? null;
        }
        return $this->$key;
    }
}
