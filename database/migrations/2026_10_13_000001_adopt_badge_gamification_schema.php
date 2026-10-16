<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adopsi skema gamifikasi berbasis badge:
     *   - badges             : katalog badge ber-key (badge_key, unik).
     *   - achievements       : unlock log per user (pivot user_id + badge_id).
     *   - challenges         : challenge harian per kategori (target + reward_xp).
     *   - challenge_progress : progres user per challenge (completed_at).
     *
     * Tabel lama (achievements bertipe, achievement_student, challenges
     * bertaut lesson, challenge_attempts) digantikan seluruhnya.
     */
    public function up(): void
    {
        Schema::dropIfExists('challenge_attempts');
        Schema::dropIfExists('achievement_student');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('challenges');

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('badge_key')->unique(); // rename dari "key" (K2 — reserved word MySQL)
            $table->string('name');
            $table->string('description', 1000)->default('');
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('unlocked_at')->useCurrent();
            $table->primary(['user_id', 'badge_id']);
        });

        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->date('challenge_date');
            $table->string('title', 200);
            $table->string('description', 1000)->default('');
            $table->enum('category', ['alfabet', 'angka', 'kosakata']);
            $table->unsignedInteger('target')->default(1);
            $table->unsignedSmallInteger('reward_xp')->default(20);
            $table->unique(['challenge_date', 'category']); // satu challenge per kategori per hari
        });

        Schema::create('challenge_progress', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('progress')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->primary(['user_id', 'challenge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_progress');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('badges');

        // Kembalikan skema lama (paritas 2026_09_26_100003).
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 16)->nullable();
            $table->enum('type', ['streak', 'xp', 'practice', 'assignment', 'special'])->default('practice');
            $table->unsignedInteger('threshold')->nullable();
            $table->timestamps();
        });

        Schema::create('achievement_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamp('unlocked_at')->useCurrent();
            $table->unique(['achievement_id', 'student_id']);
        });

        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->date('challenge_date')->unique();
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
            $table->unique(['challenge_id', 'student_id']);
        });
    }
};
