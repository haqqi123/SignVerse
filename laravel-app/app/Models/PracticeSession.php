<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeSession extends Model
{
    protected $table = 'practice_sessions';

    protected $fillable = [
        'user_id', 'material_id', 'lesson_id', 'target', 'practice_mode',
        'accuracy', 'speed', 'consistency', 'completion', 'final_score',
        'grade', 'xp_earned', 'duration_s',
    ];

    public $timestamps = false;

    protected $casts = [
        'accuracy' => 'float',
        'speed' => 'float',
        'consistency' => 'float',
        'completion' => 'float',
        'final_score' => 'float',
        'duration_s' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function gestureResults(): HasMany
    {
        return $this->hasMany(GestureResult::class, 'session_id');
    }
}
