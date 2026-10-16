<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'last_activity_date',
    ];

    protected function casts(): array
    {
        return [
            'last_activity_date' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | XP — agregat dari practice_sessions + challenge_progress + assignment,
    | tanpa kolom xp di students (desain project lama).
    | Level dari XP memakai tabel level bernama:
    | GamificationService::levelFromXp() (Beginner → ... → Inclusive Champion).
    |--------------------------------------------------------------------------
    */

    /**
     * Total XP = XP latihan selesai + reward challenge selesai + XP assignment.
     */
    public function totalXp(): int
    {
        $practiceXp = (int) $this->practiceSessions()
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->sum('xp_earned');

        $challengeXp = (int) ChallengeProgress::query()
            ->join('challenges', 'challenges.id', '=', 'challenge_progress.challenge_id')
            ->where('challenge_progress.user_id', $this->user_id)
            ->where('challenge_progress.completed', true)
            ->sum('challenges.reward_xp');

        $assignmentXp = (int) AssignmentSubmission::query()
            ->where('student_id', $this->id)
            ->where('status', AssignmentSubmission::STATUS_COMPLETED)
            ->sum('xp_earned');

        return $practiceXp + $challengeXp + $assignmentXp;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function practiceSessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class);
    }

    public function gestureResults(): HasManyThrough
    {
        return $this->hasManyThrough(GestureResult::class, PracticeSession::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
