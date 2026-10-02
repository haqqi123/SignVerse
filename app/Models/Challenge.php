<?php

namespace App\Models;

use Database\Factories\ChallengeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    /** @use HasFactory<ChallengeFactory> */
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'challenge_date',
        'title',
        'description',
        'xp_reward',
    ];

    protected function casts(): array
    {
        return [
            'challenge_date' => 'date',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ChallengeAttempt::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'challenge_attempts')
            ->withPivot('score', 'passed', 'xp_earned', 'attempted_at');
    }
}
