<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/*
 * Katalog badge gamifikasi (tabel badges).
 * badge_key = rename dari kolom "key" (keputusan K2 — reserved word MySQL).
 */
class Badge extends Model
{
    protected $fillable = ['badge_key', 'name', 'description'];

    public $timestamps = false;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'achievements', 'badge_id', 'user_id')
            ->withPivot('unlocked_at');
    }
}
