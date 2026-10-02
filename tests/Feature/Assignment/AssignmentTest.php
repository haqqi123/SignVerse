<?php

namespace Tests\Feature\Assignment;

use App\Models\Achievement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\Material;
use App\Models\PracticeSession;
use App\Models\Student;
use App\Models\User;
use App\Services\AssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 6 — assignment: guru membuat & membagikan tugas, siswa
 * menyelesaikan lewat skor sesi latihan, XP sekali, guard antar role.
 */
class AssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function teacherUser(): User
    {
        return User::factory()->teacher()->has(\App\Models\Teacher::factory(), 'teacher')->create();
    }

    private function studentUser(): User
    {
        return User::factory()->student()->has(Student::factory(), 'student')->create();
    }

    private function lesson(): Lesson
    {
        return Lesson::factory()->for(Material::factory()->for(Category::factory()))->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Service
    |--------------------------------------------------------------------------
    */

    public function test_create_and_distribute_assigns_all_students(): void
    {
        $service = app(AssignmentService::class);
        Student::factory()->count(3)->create();
        $lesson = $this->lesson();

        $assignment = $service->createAndDistribute([
            'teacher_id' => \App\Models\Teacher::factory()->create()->id,
            'lesson_id' => $lesson->id,
            'title' => 'Tugas Uji',
            'min_score' => 60,
            'xp_reward' => 30,
        ]);

        $this->assertSame(3, $assignment->submissions()->count());
        $this->assertSame(
            3,
            AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('status', AssignmentSubmission::STATUS_ASSIGNED)
                ->count(),
        );
    }

    public function test_submit_from_practice_completes_when_score_meets_threshold(): void
    {
        $service = app(AssignmentService::class);
        $student = Student::factory()->create();
        $lesson = $this->lesson();
        $assignment = $service->createAndDistribute([
            'teacher_id' => \App\Models\Teacher::factory()->create()->id,
            'lesson_id' => $lesson->id,
            'title' => 'Tugas Uji',
            'min_score' => 60,
            'xp_reward' => 30,
        ], collect([$student]));

        $session = PracticeSession::factory()->completed()->for($student)->for($lesson)->create(['score' => 85, 'xp_earned' => 0]);

        $outcome = $service->submitFromPractice($student, $assignment, $session);

        $this->assertTrue($outcome['completed']);
        $this->assertSame(30, $outcome['xp_earned']);
        $this->assertSame(AssignmentSubmission::STATUS_COMPLETED, $submission = $assignment->submissions()->where('student_id', $student->id)->first()->status);
        $this->assertSame(30, $student->totalXp()); // masuk agregat XP
    }

    public function test_submit_below_threshold_keeps_assignment_open_and_gives_no_xp(): void
    {
        $service = app(AssignmentService::class);
        $student = Student::factory()->create();
        $lesson = $this->lesson();
        $assignment = $service->createAndDistribute([
            'teacher_id' => \App\Models\Teacher::factory()->create()->id,
            'lesson_id' => $lesson->id,
            'title' => 'Tugas Uji',
            'min_score' => 60,
            'xp_reward' => 30,
        ], collect([$student]));

        $session = PracticeSession::factory()->completed()->for($student)->for($lesson)->create(['score' => 45]);

        $outcome = $service->submitFromPractice($student, $assignment, $session);

        $this->assertFalse($outcome['completed']);
        $this->assertSame(0, $outcome['xp_earned']);
        $submission = $assignment->submissions()->where('student_id', $student->id)->first();
        $this->assertSame(AssignmentSubmission::STATUS_IN_PROGRESS, $submission->status);
        $this->assertSame(45, $submission->score); // skor terakhir tercatat
    }

    public function test_resubmission_is_idempotent_after_completed(): void
    {
        $service = app(AssignmentService::class);
        $student = Student::factory()->create();
        $lesson = $this->lesson();
        $assignment = $service->createAndDistribute([
            'teacher_id' => \App\Models\Teacher::factory()->create()->id,
            'lesson_id' => $lesson->id,
            'title' => 'Tugas Uji',
            'min_score' => 60,
            'xp_reward' => 30,
        ], collect([$student]));

        $first = PracticeSession::factory()->completed()->for($student)->for($lesson)->create(['score' => 90, 'xp_earned' => 0]);
        $service->submitFromPractice($student, $assignment, $first);

        $second = PracticeSession::factory()->completed()->for($student)->for($lesson)->create(['score' => 70, 'xp_earned' => 0]);
        $outcome = $service->submitFromPractice($student, $assignment, $second);

        // Skor terbaik tetap 90, XP tetap 30 (tidak dobel).
        $submission = $assignment->submissions()->where('student_id', $student->id)->first();
        $this->assertSame(90, $submission->score);
        $this->assertSame(30, $submission->xp_earned);
        $this->assertSame(30, $student->totalXp());
    }

    public function test_assignment_completion_triggers_assignment_achievement(): void
    {
        $service = app(AssignmentService::class);
        $student = Student::factory()->create();

        Achievement::factory()->create([
            'type' => Achievement::TYPE_ASSIGNMENT,
            'threshold' => 1,
        ]);

        $lesson = $this->lesson();
        $assignment = $service->createAndDistribute([
            'teacher_id' => \App\Models\Teacher::factory()->create()->id,
            'lesson_id' => $lesson->id,
            'title' => 'Tugas Uji',
            'min_score' => 60,
            'xp_reward' => 30,
        ], collect([$student]));

        $session = PracticeSession::factory()->completed()->for($student)->for($lesson)->create(['score' => 80]);
        $outcome = $service->submitFromPractice($student, $assignment, $session);

        $this->assertTrue($outcome['completed']);
        $this->assertTrue($outcome['new_achievements']->contains('type', Achievement::TYPE_ASSIGNMENT));
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP — teacher
    |--------------------------------------------------------------------------
    */

    public function test_teacher_can_create_and_distribute_assignment_via_http(): void
    {
        $teacher = $this->teacherUser();
        Student::factory()->count(2)->create();
        $lesson = $this->lesson();

        $response = $this->actingAs($teacher)->post(route('teacher.assignments.store'), [
            'lesson_id' => $lesson->id,
            'title' => 'Latihan Alphabet',
            'min_score' => 60,
            'xp_reward' => 30,
        ]);

        $assignment = Assignment::where('title', 'Latihan Alphabet')->first();
        $response->assertRedirect(route('teacher.assignments.show', $assignment));
        $this->assertSame(2, $assignment->submissions()->count());
    }

    public function test_teacher_cannot_view_other_teachers_assignment(): void
    {
        $teacher = $this->teacherUser();
        $other = $this->teacherUser();
        $assignment = Assignment::factory()->for($other->teacher)->for($this->lesson())->create();

        $this->actingAs($teacher)->get(route('teacher.assignments.show', $assignment))->assertForbidden();
        $this->actingAs($teacher)->delete(route('teacher.assignments.destroy', $assignment))->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP — student
    |--------------------------------------------------------------------------
    */

    public function test_student_can_submit_completed_practice_via_http(): void
    {
        $teacher = $this->teacherUser();
        $user = $this->studentUser();
        $lesson = $this->lesson();

        $assignment = Assignment::factory()->for($teacher->teacher)->for($lesson)->create([
            'min_score' => 60,
            'xp_reward' => 30,
        ]);
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $user->student->id,
        ]);

        $session = PracticeSession::factory()->completed()->for($user->student)->for($lesson)->create(['score' => 92]);

        $response = $this->actingAs($user)->post(route('student.assignments.submit', $assignment), [
            'practice_session_id' => $session->id,
        ]);

        $response->assertRedirect(route('student.assignments.show', $assignment));
        $response->assertSessionHas('status');
        $this->assertSame(AssignmentSubmission::STATUS_COMPLETED,
            $assignment->submissions()->where('student_id', $user->student->id)->first()->status);
    }

    public function test_student_cannot_submit_someone_elses_session(): void
    {
        $teacher = $this->teacherUser();
        $user = $this->studentUser();
        $lesson = $this->lesson();

        $assignment = Assignment::factory()->for($teacher->teacher)->for($lesson)->create();
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $user->student->id,
        ]);

        $otherSession = PracticeSession::factory()->completed()->for(Student::factory())->for($lesson)->create(['score' => 90]);

        $this->actingAs($user)->post(route('student.assignments.submit', $assignment), [
            'practice_session_id' => $otherSession->id,
        ])->assertSessionHas('error');
    }

    public function test_student_cannot_view_assignment_they_dont_have(): void
    {
        $teacher = $this->teacherUser();
        $user = $this->studentUser();
        $assignment = Assignment::factory()->for($teacher->teacher)->for($this->lesson())->create();
        // Tugas hanya terdistribusi via submissions — user ini tidak punya baris.

        $this->actingAs($user)->get(route('student.assignments.show', $assignment))->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | Guard antar area
    |--------------------------------------------------------------------------
    */

    public function test_student_cannot_access_teacher_assignment_pages(): void
    {
        $user = $this->studentUser();

        $this->actingAs($user)->get(route('teacher.assignments.index'))
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHas('error');
    }

    public function test_teacher_cannot_access_student_assignment_pages(): void
    {
        $teacher = $this->teacherUser();

        $this->actingAs($teacher)->get(route('student.assignments.index'))
            ->assertRedirect(route('teacher.dashboard'))
            ->assertSessionHas('error');
    }
}
