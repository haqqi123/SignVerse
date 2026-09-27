<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/*
 * Paritas dengan _seed_users() di signlib/db.py (Python).
 * Password di-hash bcrypt (menggantikan SHA-256 Python) — akun & nama sama persis.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['guru@signteach.id', 'guru123', 'Budi Santoso', 'teacher'],
            ['siswa@signteach.id', 'siswa123', 'Rina Putri', 'student'],
            ['ahmad@signteach.id', 'siswa123', 'Ahmad Fauzi', 'student'],
            ['dewi@signteach.id', 'siswa123', 'Dewi Lestari', 'student'],
            ['bima@signteach.id', 'siswa123', 'Bimantara Jaya', 'student'],
            ['siti@signteach.id', 'siswa123', 'Siti Nurhaliza', 'student'],
        ];

        foreach ($users as [$username, $password, $name, $role]) {
            User::updateOrCreate(
                ['username' => $username],
                [
                    'password_hash' => Hash::make($password),
                    'name' => $name,
                    'role' => $role,
                ],
            );
        }
    }
}
