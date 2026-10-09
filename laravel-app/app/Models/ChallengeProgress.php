<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ChallengeProgress extends Pivot
{
    protected $table = 'challenge_progress';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['user_id', 'challenge_id', 'progress', 'completed', 'completed_at'];

    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
