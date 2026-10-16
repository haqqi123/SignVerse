<?php

namespace App\Models;

use Database\Factories\GestureResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GestureResult extends Model
{
    /** @use HasFactory<GestureResultFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'practice_session_id',
        'lesson_id',
        'expected_gesture',
        'recognized_gesture',
        'confidence',
        'correct',
    ];

    protected function casts(): array
    {
        return [
            'correct' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function practiceSession(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
