<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

/*
 * Paritas dengan _seed_badges() di signlib/db.py.
 * badge_key = rename dari kolom "key" (keputusan K2, reserved word MySQL).
 */
class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['first_practice', 'Langkah Pertama', 'Selesaikan latihan pertamamu.'],
            ['master_alfabet', 'Master Alfabet', '10 latihan huruf dengan akurasi ≥ 90%.'],
            ['master_angka', 'Master Angka', '10 latihan angka dengan akurasi ≥ 90%.'],
            ['sibi_explorer', 'SIBI Explorer', '5 latihan materi SIBI selesai.'],
            ['bisindo_explorer', 'BISINDO Explorer', '5 latihan materi BISINDO selesai.'],
            ['streak_7', '7 Day Streak', 'Latihan 7 hari berturut-turut.'],
            ['challenger', 'Challenger', 'Selesaikan 10 Challenge Harian.'],
            ['perfect_round', 'Putaran Sempurna', 'Akurasi 100% dalam satu latihan.'],
        ];

        foreach ($badges as [$badgeKey, $name, $description]) {
            Badge::updateOrCreate(['badge_key' => $badgeKey], ['name' => $name, 'description' => $description]);
        }
    }
}
