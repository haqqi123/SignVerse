<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * 9 tabel domain SignTeach — paritas penuh dengan SCHEMA SQLite existing
 * (signlib/db.py). Nama kolom & relasi dijaga identik supaya logika service
 * (XP, badge, challenge, assignment) dapat dipindahkan apa adanya.
 * Catatan: badges.key direname menjadi badge_key (keputusan K2 — reserved word MySQL).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['alfabet', 'angka', 'kosakata']);
            $table->enum('sign_system', ['SIBI', 'BISINDO']);
            $table->string('title');
            $table->string('description', 1000)->default('');
            $table->integer('sort_order')->default(0);
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->string('title');
            $table->string('target');
            $table->enum('practice_mode', ['letter', 'word']);
            $table->string('practice_target')->default('');
            $table->string('description', 1000)->default('');
            $table->integer('sort_order')->default(0);
        });

        Schema::create('practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
            $table->string('target');
            $table->string('practice_mode');
            $table->double('accuracy')->default(0);
            $table->double('speed')->default(0);
            $table->double('consistency')->default(0);
            $table->double('completion')->default(0);
            $table->double('final_score')->default(0);
            $table->char('grade', 1)->default('E');
            $table->integer('xp_earned')->default(0);
            $table->double('duration_s')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at'], 'idx_sessions_user');
        });

        Schema::create('gesture_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('practice_sessions')->cascadeOnDelete();
            $table->integer('seq')->default(0);
            $table->string('expected');
            $table->string('predicted');
            $table->double('confidence')->default(0);
            $table->boolean('is_correct')->default(false);
            $table->string('feedback')->default('');
            $table->integer('duration_ms')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index('session_id', 'idx_results_session');
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('badge_key')->unique();
            $table->string('name');
            $table->string('description', 1000)->default('');
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->timestamp('unlocked_at')->useCurrent();
            $table->primary(['user_id', 'badge_id']);
        });

        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->date('challenge_date');
            $table->string('title');
            $table->string('description', 1000)->default('');
            $table->enum('category', ['alfabet', 'angka', 'kosakata']);
            $table->integer('target')->default(1);
            $table->integer('reward_xp')->default(20);
            $table->unique(['challenge_date', 'category']);
        });

        Schema::create('challenge_progress', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
            $table->integer('progress')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->primary(['user_id', 'challenge_id']);
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->string('title');
            $table->string('description', 1000)->default('');
            $table->date('deadline')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('assignment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['belum dimulai', 'sedang dikerjakan', 'selesai'])->default('belum dimulai');
            $table->timestamp('completed_at')->nullable();
            $table->unique(['assignment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_items');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('challenge_progress');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('gesture_results');
        Schema::dropIfExists('practice_sessions');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('materials');
    }
};
