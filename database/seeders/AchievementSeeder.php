<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

/**
 * Definisi achievement global — konsep badge project lama.
 * Unlock terjadi otomatis di phase 5 (AchievementService).
 */
class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'name' => 'Langkah Pertama',
                'slug' => 'langkah-pertama',
                'description' => 'Menyelesaikan sesi latihan pertama.',
                'icon' => '🚀',
                'type' => Achievement::TYPE_PRACTICE,
                'threshold' => 1,
            ],
            [
                'name' => 'Rajin Latihan',
                'slug' => 'rajin-latihan',
                'description' => 'Menyelesaikan 10 sesi latihan.',
                'icon' => '💪',
                'type' => Achievement::TYPE_PRACTICE,
                'threshold' => 10,
            ],
            [
                'name' => 'Streak 7 Hari',
                'slug' => 'streak-7-hari',
                'description' => 'Berlatih 7 hari berturut-turut.',
                'icon' => '🔥',
                'type' => Achievement::TYPE_STREAK,
                'threshold' => 7,
            ],
            [
                'name' => 'Kolektor XP',
                'slug' => 'kolektor-xp',
                'description' => 'Mengumpulkan 500 XP.',
                'icon' => '⭐',
                'type' => Achievement::TYPE_XP,
                'threshold' => 500,
            ],
            [
                'name' => 'Master Isyarat',
                'slug' => 'master-isyarat',
                'description' => 'Mengumpulkan 2000 XP.',
                'icon' => '🏆',
                'type' => Achievement::TYPE_XP,
                'threshold' => 2000,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::create($achievement);
        }
    }
}
