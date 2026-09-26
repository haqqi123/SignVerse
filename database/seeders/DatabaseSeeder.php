<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Development seeder — akun demo setara project lama (db.py):
 *   guru@signteach.id / guru123  (teacher)
 *   siswa@signteach.id / siswa123 (student) + 4 student lainnya
 *
 * Data konten (categories, materials, lessons, achievements, challenges,
 * practice sessions demo) di-seed di Phase 2 sesuai ERD yang disetujui.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Teacher demo
        $teacher = User::create([
            'name' => 'Budi Santoso',
            'email' => 'guru@signteach.id',
            'password' => Hash::make('guru123'),
            'role' => User::ROLE_TEACHER,
        ]);
        $teacher->teacher()->create();

        // Student demo utama
        $student = User::create([
            'name' => 'Rina Putri',
            'email' => 'siswa@signteach.id',
            'password' => Hash::make('siswa123'),
            'role' => User::ROLE_STUDENT,
        ]);
        $student->student()->create();

        // Student demo tambahan (untuk monitoring guru & class report)
        $extraStudents = [
            ['Ahmad Fauzi', 'ahmad@signteach.id'],
            ['Dewi Lestari', 'dewi@signteach.id'],
            ['Bimantara Jaya', 'bima@signteach.id'],
            ['Siti Nurhaliza', 'siti@signteach.id'],
        ];

        foreach ($extraStudents as [$name, $email]) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('siswa123'),
                'role' => User::ROLE_STUDENT,
            ]);
            $user->student()->create();
        }
    }
}
