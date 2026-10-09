<?php

namespace App\Models;

use Database\Factories\AssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    /** @use HasFactory<AssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'lesson_id',
        'title',
        'description',
        'min_score',
        'xp_reward',
        'available_at',
        'due_at',
    ];

    protected function casts(): array
    {
        return [
            'available_at' => 'datetime',
            'due_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
