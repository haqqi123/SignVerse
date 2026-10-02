<?php

namespace App\Models;

use Database\Factories\ChallengeAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Partisipasi student pada challenge harian (unique: challenge + student).
 */
class ChallengeAttempt extends Model
{
    /** @use HasFactory<ChallengeAttemptFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'challenge_attempts';

    protected $fillable = [
        'challenge_id',
        'student_id',
        'score',
        'passed',
        'xp_earned',
        'attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'attempted_at' => 'datetime',
        ];
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
