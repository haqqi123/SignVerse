<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel latihan AI (phase 4 practice module):
     *   - practice_sessions : satu sesi latihan per lesson per student.
     *     XP di sini adalah sumber utama agregasi XP student (project lama:
     *     xp = sum(practice.xp_earned) + challenge rewards).
     *   - gesture_results   : hasil deteksi gesture per attempt dalam sesi.
     */
    public function up(): void
    {
        Schema::create('practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->enum('language', ['sibi', 'bisindo'])->default('bisindo');
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])
                ->default('in_progress');
            $table->unsignedTinyInteger('score')->nullable();      // 0-100, null selama berjalan
            $table->unsignedSmallInteger('xp_earned')->default(0);
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['student_id', 'completed_at']);
            $table->index('lesson_id');
        });

        Schema::create('gesture_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('expected_gesture', 150);
            $table->string('recognized_gesture', 150)->nullable(); // null = tidak terdeteksi
            $table->unsignedTinyInteger('confidence')->nullable(); // 0-100
            $table->boolean('correct')->default(false);
            $table->timestamp('created_at')->useCurrent();

            // Feed analisis error teacher (phase 7): gesture yang sering salah.
            $table->index(['lesson_id', 'correct']);
            $table->index(['practice_session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gesture_results');
        Schema::dropIfExists('practice_sessions');
    }
};
