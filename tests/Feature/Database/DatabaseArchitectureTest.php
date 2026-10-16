<?php

namespace Tests\Feature\Database;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Badge;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\ChallengeProgress;
use App\Models\GestureResult;
use App\Models\Lesson;
use App\Models\Material;
use App\Models\PracticeSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\GamificationService;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Phase 2 — database architecture:
 * skema, relasi antar model, agregasi XP, dan cascade delete.
 */
class DatabaseArchitectureTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Konten pembelajaran
    |--------------------------------------------------------------------------
    */

    public function test_category_has_materials_and_lessons(): void
    {
        $category = Category::factory()->create();
        $material = Material::factory()->for($category)->create();
        $lesson = Lesson::factory()->for($material)->create();

        $this->assertTrue($category->materials->contains($material));
        $this->assertTrue($material->lessons->contains($lesson));
        $this->assertSame($category->id, $lesson->material->category->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Practice
    |--------------------------------------------------------------------------
    */

    public function test_student_practice_session_has_gesture_results(): void
    {
        $student = Student::factory()->create();
        $session = PracticeSession::factory()->for($student)->completed()->create();
        $result = GestureResult::factory()->for($session)->create([
            'lesson_id' => $session->lesson_id,
            'correct' => true,
        ]);

        $this->assertTrue($student->practiceSessions->contains($session));
        $this->assertTrue($session->gestureResults->contains($result));
        $this->assertTrue($student->gestureResults->contains($result)); // hasManyThrough
    }

    /*
    |--------------------------------------------------------------------------
    | Gamifikasi
    |--------------------------------------------------------------------------
    */

    public function test_user_cannot_unlock_badge_twice(): void
    {
        $this->seed(BadgeSeeder::class);
        $badge = Badge::firstWhere('badge_key', 'first_practice');
        $user = User::factory()->student()->has(Student::factory(), 'student')->create();

        $user->badgeUsers()->attach($badge, ['unlocked_at' => now()]);

        $this->assertTrue($badge->users->contains($user));

        $this->expectException(\Illuminate\Database\QueryException::class);
        $user->badgeUsers()->attach($badge, ['unlocked_at' => now()]);
    }

    public function test_challenge_reward_xp_counts_only_completed_progress(): void
    {
        $student = Student::factory()->create();
        $challenge = Challenge::create([
            'challenge_date' => today(),
            'title' => 'Praktik Uji',
            'category' => 'alfabet',
            'target' => 5,
            'reward_xp' => 20,
        ]);

        // Progres belum tamat → XP belum masuk.
        ChallengeProgress::create([
            'user_id' => $student->user_id,
            'challenge_id' => $challenge->id,
            'progress' => 1,
            'completed' => false,
        ]);

        $this->assertSame(0, $student->totalXp());
    }

    /*
    |--------------------------------------------------------------------------
    | Agregasi XP & level (tanpa kolom xp di students)
    |--------------------------------------------------------------------------
    */

    public function test_total_xp_aggregates_practice_challenge_and_assignment(): void
    {
        $student = Student::factory()->create();
        PracticeSession::factory()->count(2)->for($student)->create(['xp_earned' => 50]);
        PracticeSession::factory()->for($student)->inProgress()->create(['xp_earned' => 999]);

        $challenge = Challenge::create([
            'challenge_date' => today(),
            'title' => 'Praktik Uji',
            'category' => 'kosakata',
            'target' => 5,
            'reward_xp' => 20,
        ]);
        ChallengeProgress::create([
            'user_id' => $student->user_id,
            'challenge_id' => $challenge->id,
            'progress' => $challenge->target,
            'completed' => true,
            'completed_at' => today(),
        ]);

        $teacher = Teacher::factory()->create();
        $assignment = Assignment::factory()->for($teacher)->for(Lesson::factory())->create(['xp_reward' => 30]);
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => AssignmentSubmission::STATUS_COMPLETED,
            'score' => 95,
            'xp_earned' => 30,
        ]);

        // 2 × 50 (practice selesai) + 20 (challenge) + 30 (assignment) = 150
        $this->assertSame(150, $student->totalXp());
        $this->assertSame('Beginner', GamificationService::levelFromXp($student->totalXp())['name']);
    }

    /*
    |--------------------------------------------------------------------------
    | Assignment
    |--------------------------------------------------------------------------
    */

    public function test_assignment_has_submissions_and_unique_per_student(): void
    {
        $teacher = Teacher::factory()->create();
        $assignment = Assignment::factory()->for($teacher)->create();
        $student = Student::factory()->create();

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => AssignmentSubmission::STATUS_SUBMITTED,
            'score' => 80,
        ]);

        $this->assertTrue($teacher->assignments->contains($assignment));
        $this->assertTrue($assignment->submissions->contains(
            AssignmentSubmission::where('student_id', $student->id)->first()
        ));

        $this->expectException(\Illuminate\Database\QueryException::class);
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => AssignmentSubmission::STATUS_SUBMITTED,
            'score' => 70,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Cascade delete
    |--------------------------------------------------------------------------
    */

    public function test_deleting_user_cascades_to_all_related_data(): void
    {
        // Profil student dibuat eksplisit (factory User tidak auto-create profil).
        $student = Student::factory()->for(User::factory()->student())->create();
        $user = $student->user;

        $session = PracticeSession::factory()->for($student)->create();
        GestureResult::factory()->for($session)->create(['lesson_id' => $session->lesson_id]);

        $challenge = Challenge::create([
            'challenge_date' => today(),
            'title' => 'Praktik Uji',
            'category' => 'alfabet',
            'target' => 5,
            'reward_xp' => 20,
        ]);
        ChallengeProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'progress' => 1,
            'completed' => false,
        ]);

        $user->delete();

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('practice_sessions', ['id' => $session->id]);
        $this->assertDatabaseMissing('gesture_results', ['practice_session_id' => $session->id]);
        $this->assertDatabaseMissing('challenge_progress', ['user_id' => $user->id]);
    }

    public function test_deleting_material_cascades_to_lessons_and_sessions(): void
    {
        $material = Material::factory()->create();
        $lesson = Lesson::factory()->for($material)->create();
        $session = PracticeSession::factory()->for(Student::factory())->create([
            'lesson_id' => $lesson->id,
        ]);

        $material->delete();

        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
        $this->assertDatabaseMissing('practice_sessions', ['id' => $session->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Seeder
    |--------------------------------------------------------------------------
    */

    public function test_database_seeder_produces_expected_data(): void
    {
        $this->seed();

        $this->assertSame(6, User::count()); // 1 guru + 5 siswa
        $this->assertTrue(Category::where('slug', 'alphabet')->exists());
        $this->assertGreaterThan(0, Lesson::count());
        $this->assertSame(8, Badge::count()); // katalog badge
        $this->assertGreaterThan(0, Challenge::count());
        $this->assertGreaterThan(0, PracticeSession::count());
    }
}
