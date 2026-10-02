<?php

namespace App\Models;

use Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    /** @use HasFactory<AchievementFactory> */
    use HasFactory;

    public const TYPE_STREAK = 'streak';
    public const TYPE_XP = 'xp';
    public const TYPE_PRACTICE = 'practice';
    public const TYPE_ASSIGNMENT = 'assignment';
    public const TYPE_SPECIAL = 'special';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'type',
        'threshold',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'achievement_student')
            ->withPivot('unlocked_at');
    }
}
