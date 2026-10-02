<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Assignment (phase 6):
     *   - assignments            : tugas dibuat teacher (isi lesson/kategori
     *     ditangani lewat item di phase 6 — untuk sekarang tugas mereferensi
     *     lesson target dan deadline).
     *   - assignment_submissions : pengerjaan student + status penilaian.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('min_score')->default(60); // syarat lulus
            $table->unsignedSmallInteger('xp_reward')->default(30);
            $table->timestamp('available_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'due_at']);
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['assigned', 'in_progress', 'submitted', 'completed'])
                ->default('assigned');
            $table->unsignedTinyInteger('score')->nullable();
            $table->unsignedSmallInteger('xp_earned')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'student_id']); // satu pengerjaan per tugas
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};
