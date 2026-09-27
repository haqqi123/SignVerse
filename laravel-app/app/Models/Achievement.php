<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/*
 * Pivot user-badge (tabel achievements). PK komposit (user_id, badge_id),
 * tanpa incrementing id — paritas dengan schema existing.
 */
class Achievement extends Pivot
{
    protected $table = 'achievements';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = ['unlocked_at' => 'datetime'];
}
