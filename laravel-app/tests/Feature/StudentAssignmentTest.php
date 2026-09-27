<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Material;
use App\Models\User;
use App\Services\AssignmentService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Test Fase 4: Validasi seluruh alur Modul Assignment Siswa di Laravel.
 */
class StudentAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_halaman_assignment_siswa_render_dengan_daftar_tugas(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/student/assignment');
        $response->assertOk();
        $response->assertSee('Assignment');
        $response->assertSee('Total Tugas');
        $response->assertSee('Belum Dimulai');
        $response->assertSee('Selesai');
    }

    public function test_siswa_bisa_mulai_tugas_dan_diarahkan_ke_practice(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $student = User::where('role', 'student')->first();
        $material = Material::first();
        $firstLesson = Lesson::where('material_id', $material->id)->first();

        $aid = AssignmentService::createAssignment(
            $teacher->id,
            $material->id,
            'Tugas Baru Siswa',
            'Kerjakan tugas ini',
            [$student->id],
            now()->addDays(5)->toDateString(),
        );

        $response = $this->actingAs($student)->post("/student/assignment/{$aid}/start");
        $response->assertRedirect(route('student.practice', ['lesson_id' => $firstLesson->id]));

        // Status berubah menjadi sedang dikerjakan
        $assignments = AssignmentService::studentAssignments($student->id);
        $task = $assignments->firstWhere('assignment_id', $aid);
        $this->assertEquals('sedang dikerjakan', $task->status);
    }

    public function test_siswa_bisa_submit_manual_tugas_selesai(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $student = User::where('role', 'student')->first();
        $material = Material::first();

        $aid = AssignmentService::createAssignment(
            $teacher->id,
            $material->id,
            'Tugas Manual Submit',
            'Deskripsi',
            [$student->id],
            now()->addDays(5)->toDateString(),
        );

        $response = $this->actingAs($student)->post("/student/assignment/{$aid}/complete");
        $response->assertRedirect();

        $assignments = AssignmentService::studentAssignments($student->id);
        $task = $assignments->firstWhere('assignment_id', $aid);
        $this->assertEquals('selesai', $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_tugas_otomatis_selesai_saat_latihan_materi_disubmit(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $student = User::where('role', 'student')->first();
        $material = Material::first();
        $lesson = Lesson::where('material_id', $material->id)->first();

        $aid = AssignmentService::createAssignment(
            $teacher->id,
            $material->id,
            'Tugas Auto Selesai',
            'Deskripsi tugas auto',
            [$student->id],
            now()->addDays(3)->toDateString(),
        );

        // Tandai sedang dikerjakan
        AssignmentService::updateAssignmentStatus($student->id, $aid, 'sedang dikerjakan');

        // Siswa menyelesaikan latihan materi tsb
        $payload = [
            'lesson_id' => $lesson->id,
            'letters' => ['S', 'A', 'Y', 'A'],
            'captures' => ['S', 'A', 'Y', 'A'],
            'confidences' => [0.90, 0.90, 0.90, 0.90],
            'durations_s' => [2.0, 2.0, 2.0, 2.0],
            'wrong_count' => 0,
        ];

        $response = $this->actingAs($student)->postJson('/student/practice/submit', $payload);
        $response->assertOk();
        $response->assertJsonFragment(['completed_assignments' => ['Tugas Auto Selesai']]);

        // Verifikasi tugas di DB sudah selesai otomatis
        $assignments = AssignmentService::studentAssignments($student->id);
        $task = $assignments->firstWhere('assignment_id', $aid);
        $this->assertEquals('selesai', $task->status);
    }
}
