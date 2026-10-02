<?php

namespace Tests\Feature\Student;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\Lesson;
use App\Models\Material;
use App\Models\PracticeSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 3 — student module: dashboard statistik, materi belajar, progress.
 */
class StudentModuleTest extends TestCase
{
    use RefreshDatabase;

    private function studentUser(): User
    {
        return User::factory()->student()->has(Student::factory(), 'student')->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Akses & guard
    |--------------------------------------------------------------------------
    */

    public function test_student_sees_dashboard_with_statistics(): void
    {
        $user = $this->studentUser();
        $student = $user->student;

        PracticeSession::factory()->count(3)->for($student)->create(['xp_earned' => 20, 'score' => 80]);

        $response = $this->actingAs($user)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['xp'] === 60
            && $summary['sessions'] === 3
            && $summary['avg_score'] === 80.0);
        $response->assertSee('Dashboard Siswa');
    }

    public function test_guest_cannot_access_student_materials(): void
    {
        $this->get(route('student.materials.index'))->assertRedirect(route('login'));
    }

    public function test_teacher_cannot_access_student_materials(): void
    {
        $teacher = User::factory()->teacher()->has(Teacher::factory(), 'teacher')->create();

        $response = $this->actingAs($teacher)->get(route('student.materials.index'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    /*
    |--------------------------------------------------------------------------
    | Materi belajar
    |--------------------------------------------------------------------------
    */

    public function test_materials_index_groups_by_category(): void
    {
        $user = $this->studentUser();
        $category = Category::factory()->create(['name' => 'Alphabet', 'order' => 0]);
        $material = Material::factory()->for($category)->create(['title' => 'Huruf A-M']);
        Lesson::factory()->count(3)->for($material)->create();

        $response = $this->actingAs($user)->get(route('student.materials.index'));

        $response->assertOk();
        $response->assertSee('Alphabet');
        $response->assertSee('Huruf A-M');
        $response->assertViewHas('categories');
    }

    public function test_materials_index_filters_by_language(): void
    {
        $user = $this->studentUser();
        $category = Category::factory()->create();
        Material::factory()->for($category)->create(['title' => 'SIBI Material', 'language' => 'sibi']);
        Material::factory()->for($category)->create(['title' => 'BISINDO Material', 'language' => 'bisindo']);

        $response = $this->actingAs($user)->get(route('student.materials.index', ['language' => 'sibi']));

        $response->assertOk();
        $response->assertSee('SIBI Material');
        $response->assertDontSee('BISINDO Material');
    }

    public function test_material_show_lists_lessons(): void
    {
        $user = $this->studentUser();
        $material = Material::factory()->create();
        Lesson::factory()->for($material)->create(['title' => 'Terima kasih', 'order' => 0]);

        $response = $this->actingAs($user)->get(route('student.materials.show', $material));

        $response->assertOk();
        $response->assertSee('Terima kasih');
        $response->assertSee($material->title);
    }

    /*
    |--------------------------------------------------------------------------
    | Progress
    |--------------------------------------------------------------------------
    */

    public function test_progress_page_shows_category_percentages(): void
    {
        $user = $this->studentUser();
        $student = $user->student;

        $category = Category::factory()->create(['order' => 0]);
        $material = Material::factory()->for($category)->create();
        $lessonA = Lesson::factory()->for($material)->create();
        Lesson::factory()->for($material)->create();

        // Lulus di 1 dari 2 lesson
        PracticeSession::factory()->for($student)->for($lessonA)->create(['score' => 80]);

        $response = $this->actingAs($user)->get(route('student.progress'));

        $response->assertOk();
        $response->assertViewHas('progress', function ($progress) {
            $row = $progress->first();

            return $row['total'] === 2 && $row['done'] === 1 && $row['percent'] === 50;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | XP & level (service)
    |--------------------------------------------------------------------------
    */

    public function test_student_xp_includes_challenge_and_assignment_rewards(): void
    {
        $student = Student::factory()->create();

        PracticeSession::factory()->for($student)->create(['xp_earned' => 40]);

        $challenge = Challenge::factory()->create(['xp_reward' => 20]);
        ChallengeAttempt::create([
            'challenge_id' => $challenge->id,
            'student_id' => $student->id,
            'score' => 90,
            'passed' => true,
            'xp_earned' => 20,
        ]);

        $teacher = Teacher::factory()->create();
        $assignment = Assignment::factory()->for($teacher)->for(Lesson::factory())->create(['xp_reward' => 30]);
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => AssignmentSubmission::STATUS_COMPLETED,
            'score' => 90,
            'xp_earned' => 30,
        ]);

        $this->assertSame(90, $student->totalXp());
        $this->assertSame(1, $student->level()); // < 100 XP
    }
}
