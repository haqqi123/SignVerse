<?php

namespace Tests\Feature\Student;

use App\Models\Achievement;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\Lesson;
use App\Models\PracticeSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Contracts\GestureRecognitionService;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 5 — gamification: challenge harian (auto-create, sekali attempt,
 * XP bonus) + achievement (unlock otomatis, idempoten) + leaderboard.
 */
class GamificationTest extends TestCase
{
    use RefreshDatabase;

    private function studentUser(): User
    {
        return User::factory()->student()->has(Student::factory(), 'student')->create();
    }

    private function swapAlwaysCorrectRecognizer(): void
    {
        $recognizer = \Mockery::mock(GestureRecognitionService::class);
        $recognizer->shouldReceive('recognize')->andReturn(
            ['recognized' => 'x', 'confidence' => 95, 'correct' => true],
        );
        $this->swap(GestureRecognitionService::class, $recognizer);
    }

    /*
    |--------------------------------------------------------------------------
    | Challenge harian
    |--------------------------------------------------------------------------
    */

    public function test_today_challenge_is_created_once_and_reused(): void
    {
        $service = app(GamificationService::class);
        Lesson::factory()->count(3)->create();

        $first = $service->todayChallenge();
        $second = $service->todayChallenge();

        $this->assertSame($first->id, $second->id);
        $this->assertSame(today()->toDateString(), $first->challenge_date->toDateString());
        $this->assertSame(1, Challenge::count());
    }

    public function test_challenge_attempt_awards_xp_once_when_passed(): void
    {
        $this->swapAlwaysCorrectRecognizer();
        $service = app(GamificationService::class);
        Lesson::factory()->create();

        $student = Student::factory()->create();
        $challenge = $service->todayChallenge();

        $first = $service->attemptChallenge($student, $challenge);
        $second = $service->attemptChallenge($student, $challenge);

        $this->assertTrue($first['passed']);
        $this->assertSame(20, $first['xp_earned']);
        $this->assertTrue($second['already_attempted']);
        $this->assertSame(1, ChallengeAttempt::where('student_id', $student->id)->count());
    }

    public function test_challenge_attempt_fails_below_threshold(): void
    {
        $recognizer = \Mockery::mock(GestureRecognitionService::class);
        $recognizer->shouldReceive('recognize')->andReturn(
            ['recognized' => 'x', 'confidence' => 40, 'correct' => false],
        );
        $this->swap(GestureRecognitionService::class, $recognizer);

        $service = app(GamificationService::class);
        Lesson::factory()->create();
        $challenge = $service->todayChallenge();

        $outcome = $service->attemptChallenge(Student::factory()->create(), $challenge);

        $this->assertFalse($outcome['passed']);
        $this->assertSame(0, $outcome['score']);
        $this->assertSame(0, $outcome['xp_earned']);
    }

    /*
    |--------------------------------------------------------------------------
    | Achievement
    |--------------------------------------------------------------------------
    */

    public function test_achievement_unlocks_when_threshold_reached(): void
    {
        $service = app(GamificationService::class);
        $student = Student::factory()->create();

        $achievement = Achievement::factory()->create([
            'type' => Achievement::TYPE_PRACTICE,
            'threshold' => 1,
        ]);

        PracticeSession::factory()->completed()->for($student)->create();

        $unlocked = $service->checkAchievements($student);

        $this->assertTrue($unlocked->contains($achievement));
        $this->assertDatabaseHas('achievement_student', [
            'achievement_id' => $achievement->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_achievement_check_is_idempotent(): void
    {
        $service = app(GamificationService::class);
        $student = Student::factory()->create();

        Achievement::factory()->create([
            'type' => Achievement::TYPE_STREAK,
            'threshold' => 1,
        ]);
        $student->forceFill(['longest_streak' => 5])->save();

        $first = $service->checkAchievements($student);
        $second = $service->checkAchievements($student);

        $this->assertSame(1, $first->count());
        $this->assertSame(0, $second->count());
    }

    public function test_xp_and_streak_achievements_use_correct_sources(): void
    {
        $service = app(GamificationService::class);
        $student = Student::factory()->create();

        $xpBadge = Achievement::factory()->create(['type' => Achievement::TYPE_XP, 'threshold' => 100]);
        $streakBadge = Achievement::factory()->create(['type' => Achievement::TYPE_STREAK, 'threshold' => 3]);
        $practiceBadge = Achievement::factory()->create(['type' => Achievement::TYPE_PRACTICE, 'threshold' => 5]);

        PracticeSession::factory()->count(2)->completed()->for($student)->create(['xp_earned' => 60]);
        $student->forceFill(['longest_streak' => 3])->save();

        $unlocked = $service->checkAchievements($student);

        $this->assertTrue($unlocked->contains($xpBadge));      // 120 XP
        $this->assertTrue($unlocked->contains($streakBadge));  // streak 3
        $this->assertFalse($unlocked->contains($practiceBadge)); // 2 < 5
    }

    /*
    |--------------------------------------------------------------------------
    | Integrasi: complete sesi latihan → achievement flash
    |--------------------------------------------------------------------------
    */

    public function test_completing_first_practice_unlocks_first_step_achievement(): void
    {
        Achievement::factory()->create([
            'name' => 'Langkah Pertama',
            'type' => Achievement::TYPE_PRACTICE,
            'threshold' => 1,
        ]);

        $recognizer = \Mockery::mock(GestureRecognitionService::class);
        $recognizer->shouldReceive('recognize')->andReturn(
            ['recognized' => 'a', 'confidence' => 95, 'correct' => true],
        );
        $this->swap(GestureRecognitionService::class, $recognizer);

        $user = $this->studentUser();
        $lesson = Lesson::factory()->for(\App\Models\Material::factory()->for(\App\Models\Category::factory()))->create();

        $this->actingAs($user)->get(route('student.practice.start', $lesson));
        $session = PracticeSession::where('student_id', $user->student->id)->sole();

        $this->actingAs($user)->postJson(route('student.practice.attempt', $session), [])->assertOk();
        $response = $this->actingAs($user)->post(route('student.practice.complete', $session));

        $response->assertRedirect(route('student.practice.result', $session));
        $response->assertSessionHas('status', fn (string $message) => str_contains($message, 'Langkah Pertama'));
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP & guard
    |--------------------------------------------------------------------------
    */

    public function test_challenge_page_renders_and_attempt_flow_works(): void
    {
        $this->swapAlwaysCorrectRecognizer();
        Lesson::factory()->create();
        $user = $this->studentUser();

        $this->actingAs($user)->get(route('student.challenge.show'))->assertOk();

        $response = $this->actingAs($user)->post(route('student.challenge.attempt'));
        $response->assertRedirect(route('student.challenge.show'));
        $response->assertSessionHas('status');

        // Attempt kedua ditolak dengan pesan sudah ikut
        $again = $this->actingAs($user)->post(route('student.challenge.attempt'));
        $again->assertRedirect(route('student.challenge.show'));
        $again->assertSessionHas('error');
    }

    public function test_achievements_page_shows_board_and_leaderboard(): void
    {
        Achievement::factory()->create(['name' => 'Langkah Pertama']);
        $user = $this->studentUser();

        $this->actingAs($user)->get(route('student.achievements.index'))
            ->assertOk()
            ->assertSee('Langkah Pertama')
            ->assertSee('Leaderboard');
    }

    public function test_teacher_cannot_access_gamification_pages(): void
    {
        $teacher = User::factory()->teacher()->has(Teacher::factory(), 'teacher')->create();

        $this->actingAs($teacher)->get(route('student.challenge.show'))
            ->assertRedirect(route('teacher.dashboard'));
        $this->actingAs($teacher)->get(route('student.achievements.index'))
            ->assertRedirect(route('teacher.dashboard'));
    }

    public function test_guest_cannot_access_gamification_pages(): void
    {
        $this->get(route('student.challenge.show'))->assertRedirect(route('login'));
        $this->get(route('student.achievements.index'))->assertRedirect(route('login'));
    }
}
