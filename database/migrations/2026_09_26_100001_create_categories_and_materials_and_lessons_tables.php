<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel konten pembelajaran (phase 3 student module):
     *   - categories   : pengelompokan materi (Alphabet, Number, Word, Phrase)
     *   - materials    : materi per kategori (SIBI/BISINDO), referensi video AI
     *   - lessons      : sub-materi granular; target latihan & XP reward
     *
     * Contoh kategori project lama: Alphabet (A-Z), Number, Word, Phrase.
     * Kolom media_path dipakai AI service (phase 9) untuk memetakan
     * gesture reference per lesson.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('icon', 16)->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index('order');
        });

        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('slug', 240)->unique();
            $table->enum('language', ['sibi', 'bisindo'])->default('bisindo');
            $table->unsignedTinyInteger('difficulty')->default(1); // 1-5
            $table->text('description')->nullable();
            $table->string('video_path')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['category_id', 'order']);
            $table->index('language');
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('gesture_label', 150); // label yang dikenali AI (phase 9)
            $table->string('video_path')->nullable();
            $table->unsignedSmallInteger('xp_reward')->default(10);
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['material_id', 'order']);
            $table->index('gesture_label');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('categories');
    }
};
