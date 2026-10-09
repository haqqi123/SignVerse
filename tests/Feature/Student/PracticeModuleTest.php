<?php

namespace Tests\Feature\Student;

use App\Models\GestureResult;
use App\Models\Lesson;
use App\Models\Material;
use App\Models\PracticeSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Contracts\GestureRecognitionService;
use App\Services\MockGestureRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 4 — practice module: start/resume sesi, attempt gesture,
 * complete (skor + XP + streak), dan guard akses.
 */
class PracticeModuleTest extends TestCase
{
    use RefreshDatabase;

    private function studentUser(): User
    {
        return User::factory()->student()->has(Student::factory(), 'student')->create();
    }

    private function lesson(): Lesson
    {
        return Lesson::factory()
            ->for(Material::factory()->for(\App\Models\Category::factory()))
            ->create(['xp_reward' => 10]);
    }

    /*
    |--------------------------------------------------------------------------
    | PracticeService (unit-ish via feature container)
    |--------------------------------------------------------------------------
    */

    public function test_recognizer_mock_rules_are_deterministic(): void
    {
        $recognizer = new MockGestureRecognitionService;

        // "halo" selalu benar
        $this->assertTrue($recognizer->recognize('halo')['correct']);

        // "fail:*" selalu salah
        $this->assertFalse($recognizer->recognize('fail:halo')['correct']);
    }

    public function test_start_session_creates_or_resumes_single_active_session(): void
    {
        $service = app(\App\Services\PracticeService::class);
        $student = Student::factory()->create();
        $lesson = $this->lesson();

        $first = $service->startSession($student, $lesson);
        $second = $service->startSession($student, $lesson);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(PracticeSession::STATUS_IN_PROGRESS, $second->status);
        $this->assertSame(1, PracticeSession::where('student_id', $student->id)->count());
    }

    public function test_attempt_stores_gesture_result_and_increments_attempts(): void
    {
        $service = app(\App\Services\PracticeService::class);
        $session = PracticeSession::factory()->inProgress()->for(Student::factory())->create(['attempts' => 0]);

        $payload = $service->attempt($session, null);

        $this->assertSame(1, $session->refresh()->attempts);
        $this->assertSame(1, GestureResult::where('practice_session_id', $session->id)->count());
        $this->assertContains($payload['result']->correct, [true, false]);
    }

    public function test_complete_pass_awards_full_xp_and_updates_streak(): void
    {
        $recognizer = \Mockery::mock(GestureRecognitionService::class);
        $recognizer->shouldReceive('recognize')->times(4)->andReturn(
            ['recognized' => 'a', 'confidence' => 95, 'correct' => true],
            ['recognized' => 'a', 'confidence' => 90, 'correct' => true],
            ['recognized' => 'a', 'confidence' => 92, 'correct' => true],
            ['recognized' => 'x', 'confidence' => 40, 'correct' => false],
        );
        $this->swap(GestureRecognitionService::class, $recognizer);

        $service = app(\App\Services\PracticeService::class);
        $student = Student::factory()->create([
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_activity_date' => null,
        ]);
        $lesson = $this->lesson(); // xp_reward = 10
        $session = $service->startSession($student, $lesson);

        for ($i = 0; $i < 4; $i++) {
            $service->attempt($session, null);
        }

        $outcome = $service->complete($session);

        $this->assertTrue($outcome['passed']);
        $this->assertSame(75, $outcome['score']); // 3 dari 4 benar
        $this->assertSame(10, $outcome['xp_earned']);
        $this->assertSame(PracticeSession::STATUS_COMPLETED, $session->refresh()->status);
        $this->assertSame(1, $student->refresh()->current_streak);
        $this->assertSame(1, $student->refresh()->longest_streak);
    }

    public function test_complete_fail_awards_no_xp(): void
    {
        $recognizer = \Mockery::mock(GestureRecognitionService::class);
        $recognizer->shouldReceive('recognize')->times(2)->andReturn(
            ['recognized' => 'a', 'confidence' => 95, 'correct' => true],
            ['recognized' => 'x', 'confidence' => 40, 'correct' => false],
        );
        $this->swap(GestureRecognitionService::class, $recognizer);

        $service = app(\App\Services\PracticeService::class);
        $session = $service->startSession(Student::factory()->create(), $this->lesson());

        $service->attempt($session, null);
        $service->attempt($session, null);
        $outcome = $service->complete($session);

        $this->assertFalse($outcome['passed']);
        $this->assertSame(50, $outcome['score']);
        $this->assertSame(0, $outcome['xp_earned']);
    }

    public function test_complete_is_idempotent(): void
    {
        $service = app(\App\Services\PracticeService::class);
        $session = PracticeSession::factory()->completed()->for(Student::factory())->create([
            'score' => 80,
            'xp_earned' => 10,
        ]);

        $outcome = $service->complete($session);

        $this->assertSame(80, $outcome['score']);
        $this->assertSame(10, $outcome['xp_earned']);
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP flow
    |--------------------------------------------------------------------------
    */

    public function test_full_practice_flow_over_http(): void
    {
        $user = $this->studentUser();
        $lesson = $this->lesson();

        // Start
        $response = $this->actingAs($user)->get(route('student.practice.start', $lesson));
        $response->assertRedirect(route('student.practice.show', ['session' => 1]));

        $session = PracticeSession::where('student_id', $user->student->id)->sole();

        // Show
        $this->actingAs($user)->get(route('student.practice.show', $session))->assertOk();

        // Attempt (mock "halo"-like deterministic path tidak dipakai; cukup valid bentuk)
        $attempt = $this->actingAs($user)->postJson(route('student.practice.attempt', $session), []);
        $attempt->assertOk()->assertJsonStructure(['correct', 'recognized', 'expected', 'confidence', 'attempts']);
        $this->assertSame(1, $session->refresh()->attempts);

        // Complete → result
        $this->actingAs($user)->post(route('student.practice.complete', $session))
            ->assertRedirect(route('student.practice.result', $session));
        $this->actingAs($user)->get(route('student.practice.result', $session))
            ->assertOk()
            ->assertSee('Hasil Latihan');
    }

    public function test_practice_index_lists_lessons(): void
    {
        $user = $this->studentUser();
        $lesson = $this->lesson();

        $this->actingAs($user)->get(route('student.practice.index'))
            ->assertOk()
            ->assertSee($lesson->title);
    }

    /*
    |--------------------------------------------------------------------------
    | Guard
    |--------------------------------------------------------------------------
    */

    public function test_student_cannot_access_other_students_session(): void
    {
        $user = $this->studentUser();
        $otherSession = PracticeSession::factory()->inProgress()->for(Student::factory())->create();

        $this->actingAs($user)->get(route('student.practice.show', $otherSession))->assertForbidden();
        $this->actingAs($user)->postJson(route('student.practice.attempt', $otherSession), [])->assertForbidden();
        $this->actingAs($user)->post(route('student.practice.complete', $otherSession))->assertForbidden();
    }

    public function test_guest_cannot_start_practice(): void
    {
        $this->get(route('student.practice.start', $this->lesson()))
            ->assertRedirect(route('login'));
    }

    public function test_teacher_cannot_access_practice(): void
    {
        $teacher = User::factory()->teacher()->has(Teacher::factory(), 'teacher')->create();

        $this->actingAs($teacher)->get(route('student.practice.index'))
            ->assertRedirect(route('teacher.dashboard'))
            ->assertSessionHas('error');
    }

    public function test_attempt_rejected_after_session_completed(): void
    {
        $user = $this->studentUser();
        $session = PracticeSession::factory()->completed()->for($user->student)->create();

        $this->actingAs($user)->postJson(route('student.practice.attempt', $session), [])
            ->assertStatus(422);
    }
}
