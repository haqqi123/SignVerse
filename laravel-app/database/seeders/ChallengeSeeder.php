<?php

namespace Database\Seeders;

use App\Models\Challenge;
use Illuminate\Database\Seeder;

/*
 * Paritas dengan _seed_challenges() di signlib/db.py:
 * challenge hari ini per kategori (idempotent — UNIQUE challenge_date+category).
 */
class ChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $today = now()->toDateString();

        $rows = [
            [$today, 'Praktik 5 huruf', 'Lakukan 5 latihan huruf alfabet hari ini.', 'alfabet', 5],
            [$today, 'Praktik 3 kosakata', 'Lakukan 3 latihan kata kosakata hari ini.', 'kosakata', 3],
        ];

        foreach ($rows as [$date, $title, $description, $category, $target]) {
            Challenge::updateOrCreate(
                ['challenge_date' => $date, 'category' => $category],
                ['title' => $title, 'description' => $description, 'target' => $target, 'reward_xp' => 20],
            );
        }
    }
}
