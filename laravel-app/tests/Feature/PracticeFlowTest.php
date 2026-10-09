<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Test Fase 3: Validasi alur AI Practice, submit sesi latihan, dan Inclusive Communication.
 */
class PracticeFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_halaman_practice_menampilkan_pilihan_tanpa_lesson_id(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/student/practice');
        $response->assertOk();
        $response->assertSee('AI Practice Room');
        $response->assertSee('Pilih Materi Latihan');
        $response->assertSee('Total Latihan');
    }

    public function test_halaman_practice_membuka_ruang_kamera_dengan_lesson_id(): void
    {
        $student = User::where('role', 'student')->first();
        $lesson = Lesson::first();

        $response = $this->actingAs($student)->get("/student/practice?lesson_id={$lesson->id}");
        $response->assertOk();
        $response->assertSee('TARGET LATIHAN');
        $response->assertSee($lesson->title);
        $response->assertSee('webcamVideo');
    }

    public function test_halaman_inclusive_communication_render(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/student/inclusive');
        $response->assertOk();
        $response->assertSee('Inclusive Communication');
        $response->assertSee('Isyarat → Suara');
        $response->assertSee('Suara / Teks → Isyarat');
    }

    public function test_submit_practice_mengkalkulasi_smart_assessment_dan_menyimpan_sesi(): void
    {
        $student = User::where('role', 'student')->first();
        $lesson = Lesson::first();

        $payload = [
            'lesson_id' => $lesson->id,
            'letters' => ['S', 'A', 'Y', 'A'],
            'captures' => ['S', 'A', 'Y', 'A'],
            'confidences' => [0.88, 0.92, 0.85, 0.90],
            'durations_s' => [2.1, 2.0, 2.4, 2.2],
            'wrong_count' => 0,
            'wrong_attempts' => [],
        ];

        $response = $this->actingAs($student)->postJson('/student/practice/submit', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('assessment.accuracy', 100)
            ->assertJsonPath('assessment.grade', 'A')
            ->assertJsonPath('xp_earned', 20);

        // Verifikasi sesi tersimpan di database
        $this->assertDatabaseHas('practice_sessions', [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'grade' => 'A',
            'accuracy' => 100,
        ]);

        // Verifikasi gesture_results tercatat
        $this->assertDatabaseHas('gesture_results', [
            'expected' => 'S',
            'predicted' => 'S',
            'is_correct' => true,
        ]);
    }
}
