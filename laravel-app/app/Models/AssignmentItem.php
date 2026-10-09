<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentItem extends Model
{
    protected $fillable = ['assignment_id', 'student_id', 'status', 'completed_at'];

    public $timestamps = false;

    protected $casts = ['completed_at' => 'datetime'];

    public const STATUS_BELUM = 'belum dimulai';
    public const STATUS_DIKERJAKAN = 'sedang dikerjakan';
    public const STATUS_SELESAI = 'selesai';

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
