<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Test L1: navigasi & struktur halaman — paritas build_navigation() app.py.
 */
class NavigationL1Test extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_siswa_menampilkan_semua_menu(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get('/student/dashboard');
        $response->assertOk();

        foreach (['Dashboard', 'Materi Belajar', 'AI Practice', 'Challenge Harian', 'Progress',
            'Achievement', 'Assignment', 'Inclusive Communication', 'Profil'] as $menu) {
            $response->assertSee($menu);
        }
    }

    public function test_sidebar_guru_menampilkan_semua_menu(): void
    {
        $user = User::factory()->teacher()->create();

        $response = $this->actingAs($user)->get('/teacher/dashboard');
        $response->assertOk();

        foreach (['Dashboard', 'Siswa', 'Monitoring', 'Assignment', 'Laporan & Report', 'Profil'] as $menu) {
            $response->assertSee($menu);
        }
    }

    public function test_semua_halaman_siswa_bisa_diakses_siswa(): void
    {
        $user = User::factory()->student()->create();

        foreach (['dashboard', 'materials', 'practice', 'challenge', 'progress',
            'achievements', 'assignment', 'inclusive', 'profile'] as $page) {
            $this->actingAs($user)->get("/student/{$page}")->assertOk();
        }
    }

    public function test_semua_halaman_guru_bisa_diakses_guru(): void
    {
        $user = User::factory()->teacher()->create();

        foreach (['dashboard', 'students', 'monitoring', 'assignments', 'reports', 'profile'] as $page) {
            $this->actingAs($user)->get("/teacher/{$page}")->assertOk();
        }
    }

    public function test_siswa_dilarang_akses_route_guru(): void
    {
        $user = User::factory()->student()->create();

        $this->actingAs($user)->get('/teacher/students')->assertForbidden();
        $this->actingAs($user)->get('/teacher/reports')->assertForbidden();
    }

    public function test_cta_coba_ai_practice_menyimpan_redirect(): void
    {
        $this->get('/go-practice')->assertRedirect('/login');
        $this->assertStringContainsString('/student/practice', session('redirect_after_login'));
    }

    public function test_redirect_after_login_dihormati_untuk_role_sesuai(): void
    {
        $user = User::factory()->student()->create();

        $this->withSession(['redirect_after_login' => route('student.practice')])
            ->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertRedirect(route('student.practice'));
    }

    public function test_redirect_after_login_diabaikan_untuk_role_beda(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->withSession(['redirect_after_login' => route('student.practice')])
            ->post('/login', ['username' => $teacher->username, 'password' => 'password'])
            ->assertRedirect(route('teacher.dashboard'));
    }

    public function test_landing_guest_menampilkan_statistik_dan_cta(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Statistik Platform')
            ->assertSee('Mulai Belajar')
            ->assertSee('Coba AI Practice');
    }
}
