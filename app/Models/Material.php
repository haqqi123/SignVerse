<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    /** @use HasFactory<MaterialFactory> */
    use HasFactory;

    public const LANGUAGE_SIBI = 'sibi';
    public const LANGUAGE_BISINDO = 'bisindo';

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'language',
        'difficulty',
        'description',
        'video_path',
        'order',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
