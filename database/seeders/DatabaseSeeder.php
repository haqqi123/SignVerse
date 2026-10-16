<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed aplikasi SignVerse — 1 guru + 5 siswa (paritas _seed_users()
 * project lama) + konten pembelajaran + achievement + data demo.
 *
 * Akun demo (dipakai tombol "Masuk demo" di halaman login):
 *   Guru : guru@signteach.id / guru123
 *   Siswa: siswa@signteach.id / siswa123
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = [
            ['guru@signteach.id', 'guru123', 'Budi Santoso', User::ROLE_TEACHER],
            ['siswa@signteach.id', 'siswa123', 'Rina Putri', User::ROLE_STUDENT],
            ['ahmad@signteach.id', 'siswa123', 'Ahmad Fauzi', User::ROLE_STUDENT],
            ['dewi@signteach.id', 'siswa123', 'Dewi Lestari', User::ROLE_STUDENT],
            ['bima@signteach.id', 'siswa123', 'Bimantara Jaya', User::ROLE_STUDENT],
            ['siti@signteach.id', 'siswa123', 'Siti Nurhaliza', User::ROLE_STUDENT],
        ];

        foreach ($users as [$email, $password, $name, $role]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => $role,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ],
            );

            // Profil 1:1 sesuai role (streak & data gamifikasi hidup di profil).
            if ($user->isTeacher()) {
                Teacher::firstOrCreate(['user_id' => $user->id]);
            } else {
                Student::firstOrCreate(['user_id' => $user->id]);
            }
        }

        $this->call([
            ContentSeeder::class,
            BadgeSeeder::class,
            DemoPracticeSeeder::class,
        ]);
    }
}
