<?php

namespace Tests\Feature\Student;

use App\Models\Badge;
use App\Models\Challenge;
use App\Models\ChallengeProgress;
use App\Models\Lesson;
use App\Models\PracticeSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ChallengeService;
use App\Services\Contracts\GestureRecognitionService;
use App\Services\GamificationService;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 5 — gamification:
 * level bernama dari XP, streak dari tanggal latihan, badge ber-key
 * (evaluateBadges idempoten), dan challenge harian per kategori
 * (bumpChallenge via latihan selesai + reward XP).
 */
class GamificationTest extends TestCase
{
    use RefreshDatabase;

    private function studentUser(): User
    {
        return User::factory()->student()->has(Student::factory(), 'student')->create();
    }

    private function swapRecognizer(array $recognition): void
    {
        $recognizer = \Mockery::mock(GestureRecognitionService::class);
        $recognizer->shouldReceive('recognize')->andReturn($recognition);
        $this->swap(GestureRecognitionService::class, $recognizer);
    }

    /*
    |--------------------------------------------------------------------------
    | Level dari XP (tabel level bernama)
    |--------------------------------------------------------------------------
    */

    public function test_level_from_xp_follows_named_thresholds(): void
    {
        $beginner = GamificationService::levelFromXp(150);
        $this->assertSame('Beginner', $beginner['name']);
        $this->assertSame(50.0, $beginner['pct']);
        $this->assertSame(300, $beginner['next_xp']);

        $this->assertSame('Explorer', GamificationService::levelFromXp(300)['name']);
        $this->assertSame('Communicator', GamificationService::levelFromXp(900)['name']);

        $top = GamificationService::levelFromXp(2500);
        $this->assertSame('Inclusive Champion', $top['name']);
        $this->assertNull($top['next_xp']);
        $this->assertSame(0.0, $top['pct']);
    }

    /*
    |--------------------------------------------------------------------------
    | XP: practice + challenge_progress + assignment
    |--------------------------------------------------------------------------
    */

    public function test_total_xp_combines_practice_challenge_and_assignment_rewards(): void
    {
        $student = Student::factory()->create();
        PracticeSession::factory()->count(2)->completed()->for($student)->create(['xp_earned' => 50]);

        $challenge = Challenge::create([
            'challenge_date' => today(),
            'title' => 'Praktik Uji',
            'category' => 'alfabet',
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
        $assignment = \App\Models\Assignment::factory()
            ->for($teacher)->for(Lesson::factory())->create(['xp_reward' => 30]);
        \App\Models\AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => \App\Models\AssignmentSubmission::STATUS_COMPLETED,
            'score' => 90,
            'xp_earned' => 30,
        ]);

        $this->assertSame(150, GamificationService::totalXp($student->user_id));
        $this->assertSame('Beginner', GamificationService::levelFromXp(150)['name']);
    }

    /*
    |--------------------------------------------------------------------------
    | Streak (dihitung dari tanggal latihan)
    |--------------------------------------------------------------------------
    */

    public function test_streak_counts_consecutive_practice_days(): void
    {
        $user = $this->studentUser();

        PracticeSession::factory()->for($user->student)->create(['created_at' => today()]);
        PracticeSession::factory()->for($user->student)->create(['created_at' => today()->subDay()]);
        PracticeSession::factory()->for($user->student)->create(['created_at' => today()->subDays(2)]);
        PracticeSession::factory()->for($user->student)->create(['created_at' => today()->subDays(9)]);

        $info = GamificationService::streakInfo($user->id);

        $this->assertSame(3, $info['streak']);
        $this->assertTrue($info['is_today']);

        // Tanpa latihan sama sekali → streak 0.
        $fresh = $this->studentUser();
        $this->assertSame(0, GamificationService::streakInfo($fresh->id)['streak']);
    }

    /*
    |--------------------------------------------------------------------------
    | Badge (evaluateBadges — idempoten)
    |--------------------------------------------------------------------------
    */

    public function test_first_practice_badge_unlocks_once_and_is_idempotent(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = $this->studentUser();

        PracticeSession::factory()->for($user->student)->create(['score' => 80]);

        GamificationService::evaluateBadges($user->id);
        GamificationService::evaluateBadges($user->id);

        $this->assertDatabaseCount('achievements', 1);
        $this->assertContains('first_practice', GamificationService::unlockedKeys($user->id));
    }

    public function test_master_alfabet_unlocks_at_ten_sessions_with_good_accuracy(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = $this->studentUser();

        // Material alfabet SIBI + 10 sesi lulus berakurasi tinggi → 3 badge sekaligus.
        $category = \App\Models\Category::factory()->create(['slug' => 'alphabet']);
        $material = \App\Models\Material::factory()->for($category)->create(['language' => 'sibi']);
        $lesson = Lesson::factory()->for($material)->create();
        PracticeSession::factory()->count(10)->for($user->student)->for($lesson)->create(['score' => 95]);

        GamificationService::evaluateBadges($user->id);

        $unlocked = GamificationService::unlockedKeys($user->id);
        $this->assertContains('master_alfabet', $unlocked);
        $this->assertContains('sibi_explorer', $unlocked); // 10 ≥ 5 sesi SIBI
        $this->assertContains('first_practice', $unlocked);
        $this->assertNotContains('master_angka', $unlocked);

        // Belum: 9 sesi saja tidak cukup.
        $other = $this->studentUser();
        $lessonOther = Lesson::factory()->for($material)->create();
        PracticeSession::factory()->count(9)->for($other->student)->for($lessonOther)->create(['score' => 95]);
        GamificationService::evaluateBadges($other->id);
        $this->assertNotContains('master_alfabet', GamificationService::unlockedKeys($other->id));
    }

    public function test_challenger_badge_requires_ten_completed_challenges(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = $this->studentUser();

        for ($i = 0; $i < 10; $i++) {
            $challenge = Challenge::create([
                'challenge_date' => today()->subDays($i)->toDateString(),
                'title' => 'Praktik #'.$i,
                'category' => 'alfabet',
                'target' => 1,
                'reward_xp' => 20,
            ]);
            ChallengeProgress::create([
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'progress' => 1,
                'completed' => true,
                'completed_at' => today()->subDays($i),
            ]);
        }

        GamificationService::evaluateBadges($user->id);

        $this->assertContains('challenger', GamificationService::unlockedKeys($user->id));

        // Sembilan challenge saja → belum unlock.
        $other = $this->studentUser();
        for ($i = 0; $i < 9; $i++) {
            $challenge = Challenge::create([
                'challenge_date' => today()->subDays($i)->toDateString(),
                'title' => 'Praktik Lain #'.$i,
                'category' => 'kosakata',
                'target' => 1,
                'reward_xp' => 20,
            ]);
            ChallengeProgress::create([
                'user_id' => $other->id,
                'challenge_id' => $challenge->id,
                'progress' => 1,
                'completed' => true,
                'completed_at' => today()->subDays($i),
            ]);
        }
        GamificationService::evaluateBadges($other->id);
        $this->assertNotContains('challenger', GamificationService::unlockedKeys($other->id));
    }

    public function test_badge_catalog_lists_all_badges_with_unlock_state(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = $this->studentUser();

        PracticeSession::factory()->for($user->student)->create(['score' => 100]);
        GamificationService::evaluateBadges($user->id);

        $badges = GamificationService::achievements($user->id);
        $byKey = collect($badges)->keyBy('key');

        $this->assertCount(8, $badges);
        $this->assertTrue($byKey['first_practice']['unlocked']);
        $this->assertTrue($byKey['perfect_round']['unlocked']); // akurasi 100
        $this->assertFalse($byKey['streak_7']['unlocked']);
    }

    /*
    |--------------------------------------------------------------------------
    | Challenge harian (ensure + bumpChallenge)
    |--------------------------------------------------------------------------
    */

    public function test_today_challenges_are_created_idempotently(): void
    {
        $first = ChallengeService::ensureTodayChallenges();
        $second = ChallengeService::ensureTodayChallenges();

        $this->assertSame(2, Challenge::count());
        $this->assertSame($first->pluck('id')->all(), $second->pluck('id')->all());
    }

    public function test_bump_challenge_progresses_and_completes_with_xp(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = $this->studentUser();
        ChallengeService::ensureTodayChallenges();

        $completed = ChallengeService::challengeStatus($user->id)->where('category', 'kosakata')->first();
        $this->assertSame(0, (int) $completed->progress);

        // 2 latihan pertama: progres naik, belum selesai.
        for ($i = 0; $i < 2; $i++) {
            $this->assertFalse(ChallengeService::bumpChallenge($user->id, 'word'));
        }

        // Latihan ke-3 → challenge kosakata tamat (target 3), unlocked.
        $this->assertTrue(ChallengeService::bumpChallenge($user->id, 'word'));

        $completed = ChallengeService::challengeStatus($user->id)->where('category', 'kosakata')->first();
        $this->assertSame(1, (int) $completed->completed);
        $this->assertNotNull($completed->completed_at);

        // Bump berulang tidak menambah progress melewati target.
        ChallengeService::bumpChallenge($user->id, 'word');
        ChallengeService::bumpChallenge($user->id, 'word');
        $this->assertSame(1, (int) ChallengeService::challengeStatus($user->id)->where('category', 'kosakata')->first()->completed);

        // Completed → reward 20 XP masuk agregat + tercatat di riwayat reward.
        $this->assertGreaterThanOrEqual(20, GamificationService::totalXp($user->id));
        $this->assertCount(1, ChallengeService::challengeRewards($user->id));
    }

    public function test_completing_practice_session_bumps_category_challenge(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = $this->studentUser();
        ChallengeService::ensureTodayChallenges();

        $this->swapRecognizer(['recognized' => 'a', 'confidence' => 95, 'correct' => true]);

        $category = \App\Models\Category::factory()->create(['slug' => 'word-slug']);
        $material = \App\Models\Material::factory()->for($category)->create();
        $lesson = Lesson::factory()->for($material)->create();

        $service = app(\App\Services\PracticeService::class);
        $student = $user->student;
        $session = $service->startSession($student, $lesson);

        for ($i = 0; $i < 4; $i++) {
            $service->attempt($session, null);
        }
        $service->complete($session);

        // Kategori slug tidak dikenal → tidak ada challenge yang naik (aman),
        // tapi badge first_practice terbuka.
        $this->assertContains('first_practice', GamificationService::unlockedKeys($user->id));

        // Latihan kategori alfabet (slug 'alphabet') menaikkan challenge alfabet.
        $alfabetCategory = \App\Models\Category::factory()->create(['slug' => 'alphabet']);
        $alfabetMaterial = \App\Models\Material::factory()->for($alfabetCategory)->create(['language' => 'sibi']);
        $alfabetLesson = Lesson::factory()->for($alfabetMaterial)->create();
        $session2 = $service->startSession($student, $alfabetLesson);

        for ($i = 0; $i < 4; $i++) {
            $service->attempt($session2, null);
        }
        $service->complete($session2);

        $status = ChallengeService::challengeStatus($user->id)->where('category', 'alfabet')->sole();
        $this->assertSame(1, (int) $status->progress);
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP & guard
    |--------------------------------------------------------------------------
    */

    public function test_challenge_page_renders_daily_challenges(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = $this->studentUser();

        $this->actingAs($user)->get(route('student.challenge'))
            ->assertOk()
            ->assertSee('Challenge Harian')
            ->assertSee('Praktik 5 huruf')
            ->assertSee('Streak Kamu Saat Ini');
    }

    public function test_achievements_page_shows_badges_level_and_leaderboard(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = $this->studentUser();

        PracticeSession::factory()->for($user->student)->create(['score' => 90, 'xp_earned' => 20]);
        GamificationService::evaluateBadges($user->id);

        $this->actingAs($user)->get(route('student.achievements.index'))
            ->assertOk()
            ->assertSee('Langkah Pertama')
            ->assertSee('Beginner')
            ->assertSee('Leaderboard');
    }

    public function test_practice_pipeline_flashes_new_badge(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = $this->studentUser();

        $this->swapRecognizer(['recognized' => 'a', 'confidence' => 95, 'correct' => true]);

        $category = \App\Models\Category::factory()->create(['slug' => 'alphabet']);
        $material = \App\Models\Material::factory()->for($category)->create();
        $lesson = Lesson::factory()->for($material)->create();

        $this->actingAs($user)->get(route('student.practice.start', $lesson));
        $session = PracticeSession::where('student_id', $user->student->id)->sole();

        $this->actingAs($user)->postJson(route('student.practice.attempt', $session), [])->assertOk();
        $response = $this->actingAs($user)->post(route('student.practice.complete', $session));

        $response->assertRedirect(route('student.practice.result', $session));
        $response->assertSessionHas('status', fn (string $message) => str_contains($message, 'Badge baru: Langkah Pertama'));
    }

    public function test_teacher_cannot_access_gamification_pages(): void
    {
        $teacher = User::factory()->teacher()->has(Teacher::factory(), 'teacher')->create();

        $this->actingAs($teacher)->get(route('student.challenge'))
            ->assertRedirect(route('teacher.dashboard'));
        $this->actingAs($teacher)->get(route('student.achievements.index'))
            ->assertRedirect(route('teacher.dashboard'));
    }

    public function test_guest_cannot_access_gamification_pages(): void
    {
        $this->get(route('student.challenge'))->assertRedirect(route('login'));
        $this->get(route('student.achievements.index'))->assertRedirect(route('login'));
    }
}
