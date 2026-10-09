<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Test Fase 5: Validasi seluruh alur dan modul Guru di Laravel.
 */
class TeacherFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_teacher_dashboard_render_metrik_kelas(): void
    {
        $teacher = User::where('role', 'teacher')->first();

        $response = $this->actingAs($teacher)->get('/teacher/dashboard');
        $response->assertOk();
        $response->assertSee('Dashboard Guru');
        $response->assertSee('Total Siswa');
        $response->assertSee('Rata-rata Skor Kelas');
        $response->assertSee('Latihan Minggu Ini');
    }

    public function test_teacher_students_render_daftar_dan_keaktifan(): void
    {
        $teacher = User::where('role', 'teacher')->first();

        $response = $this->actingAs($teacher)->get('/teacher/students');
        $response->assertOk();
        $response->assertSee('Daftar Siswa');
        $response->assertSee('Akurasi Rata-rata');
    }

    public function test_teacher_monitoring_render_detail_siswa(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($teacher)->get("/teacher/monitoring?student_id={$student->id}");
        $response->assertOk();
        $response->assertSee('Monitoring Siswa');
        $response->assertSee($student->name);
        $response->assertSee('Akurasi per Kategori Materi');
    }

    public function test_teacher_assignments_dan_pembuatan_tugas_baru(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $student = User::where('role', 'student')->first();
        $material = Material::first();

        // 1. Render halaman tugas
        $response = $this->actingAs($teacher)->get('/teacher/assignments');
        $response->assertOk();
        $response->assertSee('Manajemen Assignment');
        $response->assertSee('Buat Assignment Baru');

        // 2. Buat tugas baru via POST
        $createResponse = $this->actingAs($teacher)->post('/teacher/assignments', [
            'material_id' => $material->id,
            'title' => 'Tugas Uji Coba Guru',
            'description' => 'Deskripsi penugasan kelas',
            'student_ids' => [$student->id],
            'deadline' => now()->addDays(7)->toDateString(),
        ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('assignments', [
            'teacher_id' => $teacher->id,
            'title' => 'Tugas Uji Coba Guru',
        ]);
    }

    public function test_teacher_reports_dan_ekspor_csv(): void
    {
        $teacher = User::where('role', 'teacher')->first();

        // 1. Render halaman laporan
        $response = $this->actingAs($teacher)->get('/teacher/reports');
        $response->assertOk();
        $response->assertSee('Laporan & Rekapitulasi Kelas');
        $response->assertSee('Export Class Report (CSV)');

        // 2. Ekspor CSV
        $exportResponse = $this->actingAs($teacher)->get('/teacher/reports/export');
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
    }

    public function test_teacher_profile_dan_update_profil(): void
    {
        $teacher = User::where('role', 'teacher')->first();

        // 1. Render profil guru
        $response = $this->actingAs($teacher)->get('/teacher/profile');
        $response->assertOk();
        $response->assertSee('Profil Guru');
        $response->assertSee($teacher->name);

        // 2. Update profil guru
        $updateResponse = $this->actingAs($teacher)->post('/teacher/profile', [
            'name' => 'Budi Santoso S.Pd.',
            'password' => '',
            'password_confirmation' => '',
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'name' => 'Budi Santoso S.Pd.',
        ]);
    }
}
