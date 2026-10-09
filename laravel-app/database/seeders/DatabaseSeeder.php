<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/*
 * Urutan seeding SignTeach — paritas init_db() di signlib/db.py:
 * users → materials+lessons → badges → challenges → demo history (+badge sync).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MaterialLessonSeeder::class,
            BadgeSeeder::class,
            ChallengeSeeder::class,
            DemoHistorySeeder::class,
        ]);
    }
}
