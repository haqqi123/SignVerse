<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['username', 'password_hash', 'name', 'role'];

    public $timestamps = false;

    protected $hidden = ['password_hash', 'remember_token'];

    public function practiceSessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'achievements', 'user_id', 'badge_id')
            ->withPivot('unlocked_at');
    }

    public function challengeProgress(): HasMany
    {
        return $this->hasMany(ChallengeProgress::class);
    }

    public function assignmentsAsTeacher(): HasMany
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    public function assignmentItems(): HasMany
    {
        return $this->hasMany(AssignmentItem::class, 'student_id');
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
}
