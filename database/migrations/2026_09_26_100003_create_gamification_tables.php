<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gamifikasi (phase 5):
     *   - achievements            : definisi badge global.
     *   - achievement_student     : unlock log (pivot).
     *   - challenges              : challenge harian per tanggal (dipilih
     *     deterministik dari lesson — konsep daily challenge project lama).
     *   - challenge_attempts      : partisipasi student pada challenge.
     */
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 16)->nullable();
            $table->enum('type', ['streak', 'xp', 'practice', 'assignment', 'special'])
                ->default('practice');
            $table->unsignedInteger('threshold')->nullable(); // mis. streak 7 hari / XP 500
            $table->timestamps();
        });

        Schema::create('achievement_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamp('unlocked_at')->useCurrent();

            $table->unique(['achievement_id', 'student_id']);
            $table->index('student_id');
        });

        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->date('challenge_date')->unique(); // satu challenge per hari, global
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('xp_reward')->default(20);
            $table->timestamps();
        });

        Schema::create('challenge_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0);
            $table->boolean('passed')->default(false);
            $table->unsignedSmallInteger('xp_earned')->default(0);
            $table->timestamp('attempted_at')->useCurrent();

            $table->unique(['challenge_id', 'student_id']); // sekali per hari
            $table->index(['student_id', 'attempted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_attempts');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('achievement_student');
        Schema::dropIfExists('achievements');
    }
};
