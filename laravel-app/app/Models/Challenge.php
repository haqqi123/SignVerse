<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    protected $fillable = ['challenge_date', 'title', 'description', 'category', 'target', 'reward_xp'];

    public $timestamps = false;

    protected $casts = ['challenge_date' => 'date:Y-m-d'];

    public function progress(): HasMany
    {
        return $this->hasMany(ChallengeProgress::class);
    }
}
