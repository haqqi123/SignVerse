<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GestureResult extends Model
{
    protected $table = 'gesture_results';

    protected $fillable = ['session_id', 'seq', 'expected', 'predicted', 'confidence', 'is_correct', 'feedback', 'duration_ms'];

    public $timestamps = false;

    protected $casts = [
        'confidence' => 'float',
        'is_correct' => 'boolean',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class, 'session_id');
    }
}
