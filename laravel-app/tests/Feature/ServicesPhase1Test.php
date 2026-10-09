<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AssignmentService;
use App\Services\ChallengeService;
use App\Services\GamificationService;
use App\Services\LearningService;
use App\Services\ScoringService;
use App\Services\StudentService;
use App\Services\TeacherService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Test Fase 1: Validasi seluruh Service Layer domain SignTeach di Laravel.
 */
class ServicesPhase1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_learning_service_mengambil_materi_dan_pelajaran(): void
    {
        $materials = LearningService::getMaterials();
        $this->assertNotEmpty($materials);

        $firstMaterial = $materials->first();
        $lessons = LearningService::getLessons($firstMaterial->id);
        $this->assertNotEmpty($lessons);

        $firstLesson = LearningService::firstLesson($firstMaterial->id);
        $this->assertNotNull($firstLesson);
        $this->assertEquals($lessons->first()->id, $firstLesson->id);
    }

    public function test_student_service_menghitung_statistik_dan_rekomendasi(): void
    {
        $student = User::where('role', 'student')->first();
        $this->assertNotNull($student);

        $stats = StudentService::studentStats($student->id);
        $this->assertArrayHasKey('total_sessions', $stats);
        $this->assertArrayHasKey('avg_accuracy', $stats);
        $this->assertArrayHasKey('materials_done', $stats);
        $this->assertArrayHasKey('challenges_done', $stats);

        $recommendation = StudentService::recommendation($student->id);
        $this->assertArrayHasKey('title', $recommendation);
        $this->assertArrayHasKey('text', $recommendation);

        $history = StudentService::practiceHistory($student->id, 5);
        $this->assertIsIterable($history);
    }

    public function test_gamification_service_menghitung_xp_level_streak_dan_badge(): void
    {
        $student = User::where('role', 'student')->first();
        $this->assertNotNull($student);

        $xp = GamificationService::totalXp($student->id);
        $this->assertGreaterThanOrEqual(0, $xp);

        $level = GamificationService::levelFromXp($xp);
        $this->assertArrayHasKey('name', $level);
        $this->assertArrayHasKey('color', $level);
        $this->assertArrayHasKey('pct', $level);

        $streak = GamificationService::streakInfo($student->id);
        $this->assertArrayHasKey('streak', $streak);

        $progress = GamificationService::badgeProgress($student->id);
        $this->assertArrayHasKey('first_practice', $progress);
        $this->assertArrayHasKey('streak_7', $progress);

        $achievements = GamificationService::achievements($student->id);
        $this->assertNotEmpty($achievements);
    }

    public function test_scoring_service_menghitung_smart_assessment_secara_akurat(): void
    {
        $letters = ['S', 'A', 'Y', 'A'];
        $captures = ['S', 'A', 'Y', 'A'];
        $confidences = [0.85, 0.90, 0.88, 0.92];
        $durations = [2.0, 2.5, 3.0, 2.8];

        $assessment = ScoringService::computeAssessment($letters, $captures, $confidences, $durations, 0);

        $this->assertEquals(100.0, $assessment['accuracy']);
        $this->assertEquals(100.0, $assessment['completion']);
        $this->assertEquals('A', $assessment['grade']);
        $this->assertGreaterThanOrEqual(90.0, $assessment['final']);

        $rec = ScoringService::recommendation($assessment);
        $this->assertNotEmpty($rec);
    }

    public function test_challenge_service_bisa_bump_progress(): void
    {
        $student = User::where('role', 'student')->first();
        $this->assertNotNull($student);

        // Ambil status awal
        $statusBefore = ChallengeService::challengeStatus($student->id);
        $this->assertNotEmpty($statusBefore);

        $firstChallenge = $statusBefore->first();
        $category = $firstChallenge->category;

        // Bump progress
        ChallengeService::bumpChallenge($student->id, $category);

        $statusAfter = ChallengeService::challengeStatus($student->id);
        $updatedChallenge = $statusAfter->firstWhere('id', $firstChallenge->id);

        $this->assertGreaterThanOrEqual($firstChallenge->progress, $updatedChallenge->progress);
    }

    public function test_assignment_service_alur_buat_dan_auto_complete(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $student = User::where('role', 'student')->first();
        $material = LearningService::getMaterials()->first();

        // 1. Buat tugas
        $assignmentId = AssignmentService::createAssignment(
            $teacher->id,
            $material->id,
            'Tugas Alfabet Baru',
            'Selesaikan latihan modul ini',
            [$student->id],
            now()->addDays(7)->toDateString(),
        );

        $this->assertGreaterThan(0, $assignmentId);

        // 2. Cek tugas siswa
        $assignments = AssignmentService::studentAssignments($student->id);
        $assigned = $assignments->firstWhere('assignment_id', $assignmentId);
        $this->assertNotNull($assigned);
        $this->assertEquals('belum dimulai', $assigned->status);

        // 3. Siswa mulai mengerjakan
        AssignmentService::updateAssignmentStatus($student->id, $assignmentId, 'sedang dikerjakan');

        // 4. Auto-complete saat menyelesaikan materi
        $completedTitles = AssignmentService::completeAssignmentsForMaterial($student->id, $material->id);
        $this->assertContains('Tugas Alfabet Baru', $completedTitles);

        // 5. Cek status menjadi selesai
        $updatedAssignments = AssignmentService::studentAssignments($student->id);
        $updated = $updatedAssignments->firstWhere('assignment_id', $assignmentId);
        $this->assertEquals('selesai', $updated->status);
        $this->assertNotNull($updated->completed_at);
    }

    public function test_teacher_service_menghasilkan_ringkasan_dan_laporan(): void
    {
        $summary = TeacherService::teacherSummary();
        $this->assertArrayHasKey('students', $summary);
        $this->assertArrayHasKey('avg_score', $summary);
        $this->assertArrayHasKey('weekly_sessions', $summary);
        $this->assertArrayHasKey('active_assignments', $summary);

        $report = TeacherService::classReport();
        $this->assertNotEmpty($report);
        $this->assertArrayHasKey('accuracy', $report[0]);
        $this->assertArrayHasKey('score', $report[0]);

        $student = User::where('role', 'student')->first();
        $detail = TeacherService::studentDetail($student->id);
        $this->assertNotNull($detail);
        $this->assertEquals($student->id, $detail['id']);
        $this->assertArrayHasKey('stats', $detail);
    }
}
