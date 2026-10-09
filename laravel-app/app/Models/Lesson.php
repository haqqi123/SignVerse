<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    protected $fillable = ['material_id', 'title', 'target', 'practice_mode', 'practice_target', 'description', 'sort_order'];

    public $timestamps = false;

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
