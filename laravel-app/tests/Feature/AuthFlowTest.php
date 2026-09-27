<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Test L0: auth + role guard — setara alur signlib/auth.py.
 * (Pengganti _login_flow_test.py versi Python.)
 */
class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_tampil_untuk_guest(): void
    {
        $this->get('/')->assertOk()->assertSee('SignTeach');
    }

    public function test_guest_diarahkan_ke_login_saat_buka_dashboard(): void
    {
        $this->get('/student/dashboard')->assertRedirect('/login');
        $this->get('/teacher/dashboard')->assertRedirect('/login');
    }

    public function test_login_siswa_berhasil_ke_dashboard_siswa(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ])->assertRedirect('/student/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_guru_berhasil_ke_dashboard_guru(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ])->assertRedirect('/teacher/dashboard');
    }

    public function test_login_password_salah_ditolak(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->from('/login')->post('/login', [
            'username' => $user->username,
            'password' => 'salah-sekali',
        ])->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_siswa_dilarang_akses_dashboard_guru(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)->get('/teacher/dashboard')->assertForbidden();
    }

    public function test_guru_dilarang_akses_dashboard_siswa(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($user)->get('/student/dashboard')->assertForbidden();
    }

    public function test_registrasi_membuat_akun_siswa(): void
    {
        $this->post('/register', [
            'name' => 'Siswa Baru',
            'username' => 'baru@example.com',
            'password' => 'rahasia',
            'password_confirmation' => 'rahasia',
        ])->assertRedirect('/student/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'baru@example.com',
            'name' => 'Siswa Baru',
            'role' => 'student',
        ]);
    }

    public function test_logout_mengakhiri_session(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_halaman_login_menampilkan_ui_modern_dan_kredensial_demo(): void
    {
        $response = $this->get('/login');
        $response->assertOk()
            ->assertSee('Masuk ke SignTeach')
            ->assertSee('Akses Cepat Akun Demo')
            ->assertSee('siswa@signteach.id')
            ->assertSee('guru@signteach.id')
            ->assertDontSee('<aside class="sidebar">', false);
    }
}
