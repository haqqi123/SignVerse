<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Test Fase 2: Validasi seluruh tampilan halaman Siswa di Laravel.
 */
class StudentPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_student_dashboard_menampilkan_data_dan_ringkasan(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/student/dashboard');
        $response->assertOk();
        $response->assertSee('Progress Belajar');
        $response->assertSee('Level');
        $response->assertSee('Total XP');
        $response->assertSee('Total Latihan');
        $response->assertSee('Daily Challenge');
    }

    public function test_student_materials_menampilkan_katalog_dan_filter(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/student/materials');
        $response->assertOk();
        $response->assertSee('Materi Belajar');
        $response->assertSee('Alfabet');
        $response->assertSee('Angka');
        $response->assertSee('SIBI');

        // Test filter category
        $filtered = $this->actingAs($student)->get('/student/materials?category=alfabet');
        $filtered->assertOk();
        $filtered->assertSee('Alfabet');
    }

    public function test_student_achievements_menampilkan_badge(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/student/achievements');
        $response->assertOk();
        $response->assertSee('Achievement');
        $response->assertSee('Badge Terbuka');
        $response->assertSee('Total XP');
    }

    public function test_student_challenge_menampilkan_tantangan_harian(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/student/challenge');
        $response->assertOk();
        $response->assertSee('Challenge Harian');
        $response->assertSee('Streak');
    }

    public function test_student_progress_menampilkan_riwayat_dan_akurasi(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/student/progress');
        $response->assertOk();
        $response->assertSee('Progress Belajar');
        $response->assertSee('Akurasi per Kategori');
        $response->assertSee('Riwayat Latihan');
    }

    public function test_student_profile_dan_update_profil(): void
    {
        $student = User::where('role', 'student')->first();

        // 1. Render halaman profil
        $response = $this->actingAs($student)->get('/student/profile');
        $response->assertOk();
        $response->assertSee($student->name);
        $response->assertSee('Pengaturan Profil');

        // 2. Update nama
        $updateResponse = $this->actingAs($student)->post('/student/profile', [
            'name' => 'Nama Siswa Terupdate',
            'password' => '',
            'password_confirmation' => '',
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'Nama Siswa Terupdate',
        ]);
    }
}
