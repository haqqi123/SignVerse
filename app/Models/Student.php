<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    | XP & level — agregat dari practice_sessions + challenge/assignment
    | rewards (tanpa kolom xp di students, sesuai desain project lama).
    |--------------------------------------------------------------------------
    */

    /**
     * Total XP = XP latihan + XP challenge + XP assignment selesai.
     */
    public function totalXp(): int
    {
        $practiceXp = (int) $this->practiceSessions()
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->sum('xp_earned');
        $challengeXp = (int) ChallengeAttempt::query()
            ->where('student_id', $this->id)
            ->where('passed', true)
            ->sum('xp_earned');
        $assignmentXp = (int) AssignmentSubmission::query()
            ->where('student_id', $this->id)
            ->where('status', AssignmentSubmission::STATUS_COMPLETED)
            ->sum('xp_earned');

        return $practiceXp + $challengeXp + $assignmentXp;
    }

    /**
     * Level dari XP (level n butuh n * 100 XP kumulatif — rumus project lama).
     */
    public function level(): int
    {
        return intdiv(max($this->totalXp(), 0), 100) + 1;
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

    public function challengeAttempts(): HasMany
    {
        return $this->hasMany(ChallengeAttempt::class);
    }

    public function challenges(): BelongsToMany
    {
        return $this->belongsToMany(Challenge::class, 'challenge_attempts')
            ->withPivot('score', 'passed', 'xp_earned', 'attempted_at');
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'achievement_student')
            ->withPivot('unlocked_at');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class, 'assignment_submissions')
            ->withPivot('status', 'score', 'xp_earned', 'submitted_at', 'completed_at');
    }
}
