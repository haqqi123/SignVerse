<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/*
 * Challenge harian per kategori (tabel challenges):
 * satu challenge (tanggal + kategori unik) dengan target jumlah latihan
 * dan reward XP. Progres user hidup di challenge_progress.
 */
class Challenge extends Model
{
    protected $fillable = ['challenge_date', 'title', 'description', 'category', 'target', 'reward_xp'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'challenge_date' => 'date:Y-m-d',
        ];
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ChallengeProgress::class);
    }
}
