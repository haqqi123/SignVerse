<?php

namespace App\Support;

/*
 * Menu aplikasi — paritas build_navigation() di app.py (Python):
 * guest → Beranda/Masuk/Daftar; siswa → 9 halaman; guru → 6 halaman.
 * Semua halaman sudah punya route (L0+L1); isinya dimigrasikan bertahap L2–L6.
 */
class Navigation
{
    public static function items(?string $role): array
    {
        if ($role === 'student') {
            return [
                ['route' => 'student.dashboard', 'title' => 'Dashboard', 'icon' => '🏠'],
                ['route' => 'student.materials', 'title' => 'Materi Belajar', 'icon' => '📚'],
                ['route' => 'student.practice', 'title' => 'AI Practice', 'icon' => '✋'],
                ['route' => 'student.challenge', 'title' => 'Challenge Harian', 'icon' => '🎯'],
                ['route' => 'student.progress', 'title' => 'Progress', 'icon' => '📈'],
                ['route' => 'student.achievements', 'title' => 'Achievement', 'icon' => '🏆'],
                ['route' => 'student.assignment', 'title' => 'Assignment', 'icon' => '📝'],
                ['route' => 'student.inclusive', 'title' => 'Inclusive Communication', 'icon' => '💬'],
                ['route' => 'student.profile', 'title' => 'Profil', 'icon' => '👤'],
            ];
        }

        if ($role === 'teacher') {
            return [
                ['route' => 'teacher.dashboard', 'title' => 'Dashboard', 'icon' => '🏠'],
                ['route' => 'teacher.students', 'title' => 'Siswa', 'icon' => '👥'],
                ['route' => 'teacher.monitoring', 'title' => 'Monitoring', 'icon' => '🧑‍🎓'],
                ['route' => 'teacher.assignments', 'title' => 'Assignment', 'icon' => '📋'],
                ['route' => 'teacher.reports', 'title' => 'Laporan & Report', 'icon' => '📊'],
                ['route' => 'teacher.profile', 'title' => 'Profil', 'icon' => '👤'],
            ];
        }

        return [
            ['route' => 'home', 'title' => 'Beranda', 'icon' => '🏠'],
            ['route' => 'login', 'title' => 'Masuk', 'icon' => '🔑'],
            ['route' => 'register', 'title' => 'Daftar', 'icon' => '📝'],
        ];
    }
}
