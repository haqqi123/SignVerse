<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\Lesson;
use App\Models\Student;
use App\Services\Contracts\GestureRecognitionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Gamifikasi (phase 5): challenge harian + achievement.
 *
 * Challenge harian global (satu per tanggal, unique di DB) — lesson dipilih
 * deterministik dengan rotasi ID (konsep daily challenge project lama).
 * Attempt sekali per student per challenge (unique constraint).
 *
 * XP student = practice (PracticeService) + challenge passed + assignment
 * completed — agregat via Student::totalXp(), tanpa kolom xp di students.
 */
class GamificationService
{
    public const CHALLENGE_PASS_SCORE = 60;

    public function __construct(
        private GestureRecognitionService $recognizer,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Challenge harian
    |--------------------------------------------------------------------------
    */

    /**
     * Challenge untuk hari ini — dibuat otomatis jika belum ada.
     */
    public function todayChallenge(): Challenge
    {
        return $this->createForDate(today()->toDateString());
    }

    /**
     * Buat challenge untuk tanggal tertentu: lesson dipilih rotasi
     * berdasarkan ID tertinggi (deterministik & stabil antar request).
     */
    public function createForDate(string $date): Challenge
    {
        return DB::transaction(function () use ($date) {
            // whereDate agar konsisten di MySQL maupun SQLite (format penyimpanan
            // kolom date bisa berbeda antar driver).
            $existing = Challenge::whereDate('challenge_date', $date)->first();
            if ($existing) {
                return $existing;
            }

            $maxId = (int) Lesson::max('id');
            abort_if($maxId === 0, 422, 'Belum ada lesson untuk challenge.');

            $lesson = Lesson::find(($date === today()->toDateString() ? today()->format('Ymd') : (int) strtotime($date)) % $maxId + 1);

            return Challenge::create([
                'lesson_id' => $lesson->id,
                'challenge_date' => $date,
                'title' => 'Challenge Harian: '.$lesson->title,
                'description' => 'Latihan isyarat "'.$lesson->title.'" hari ini dan raih XP bonus!',
                'xp_reward' => 20,
            ]);
        });
    }

    /**
     * Attempt challenge oleh student — sekali per challenge (unique).
     *
     * @return array{attempt: ChallengeAttempt, passed: bool, score: int, xp_earned: int, already_attempted: bool}
     */
    public function attemptChallenge(Student $student, Challenge $challenge, ?string $imageData = null): array
    {
        $existing = ChallengeAttempt::where('challenge_id', $challenge->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return [
                'attempt' => $existing,
                'passed' => $existing->passed,
                'score' => $existing->score,
                'xp_earned' => $existing->xp_earned,
                'already_attempted' => true,
            ];
        }

        // 3 attempt gesture seperti sesi latihan biasa.
        $correct = 0;
        $total = 3;
        for ($i = 0; $i < $total; $i++) {
            $recognition = $this->recognizer->recognize($challenge->lesson->gesture_label, $imageData);
            $correct += $recognition['correct'] ? 1 : 0;
        }

        $score = (int) round($correct / $total * 100);
        $passed = $score >= self::CHALLENGE_PASS_SCORE;
        $xp = $passed ? $challenge->xp_reward : 0;

        $attempt = ChallengeAttempt::create([
            'challenge_id' => $challenge->id,
            'student_id' => $student->id,
            'score' => $score,
            'passed' => $passed,
            'xp_earned' => $xp,
            'attempted_at' => now(),
        ]);

        return [
            'attempt' => $attempt,
            'passed' => $passed,
            'score' => $score,
            'xp_earned' => $xp,
            'already_attempted' => false,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Achievement
    |--------------------------------------------------------------------------
    */

    /**
     * Cek semua achievement, unlock yang terpenuhi, kembalikan yang BARU.
     *
     * @return Collection<int, Achievement>
     */
    public function checkAchievements(Student $student): Collection
    {
        $unlockedIds = $student->achievements()->pluck('achievements.id');

        $candidates = Achievement::whereNotIn('id', $unlockedIds)->get();
        if ($candidates->isEmpty()) {
            return collect();
        }

        [$practices, $xp, $streak, $assignmentsDone] = [
            $this->completedPracticeCount($student),
            $student->totalXp(),
            $student->longest_streak,
            $this->completedAssignmentCount($student),
        ];

        $newlyUnlocked = $candidates->filter(function (Achievement $achievement) use ($practices, $xp, $streak, $assignmentsDone) {
            return match ($achievement->type) {
                Achievement::TYPE_PRACTICE => $practices >= $achievement->threshold,
                Achievement::TYPE_XP => $xp >= $achievement->threshold,
                Achievement::TYPE_STREAK => $streak >= $achievement->threshold,
                Achievement::TYPE_ASSIGNMENT => $assignmentsDone >= $achievement->threshold,
                Achievement::TYPE_SPECIAL => false, // diberikan manual
                default => false,
            };
        });

        if ($newlyUnlocked->isEmpty()) {
            return collect();
        }

        $now = now();
        $student->achievements()->attach(
            $newlyUnlocked->pluck('id'),
            ['unlocked_at' => $now],
        );

        return $newlyUnlocked->values();
    }

    /**
     * Semua achievement + status unlock untuk student (grid UI).
     */
    public function achievementBoard(Student $student): Collection
    {
        $unlocked = $student->achievements()->get()->keyBy('id');

        return Achievement::orderBy('type')->orderBy('threshold')->get()
            ->map(fn (Achievement $achievement) => [
                'achievement' => $achievement,
                'unlocked_at' => $unlocked[$achievement->id]?->pivot->unlocked_at ?? null,
            ]);
    }

    /**
     * Leaderboard XP antar student (untuk widget dashboard).
     */
    public function leaderboard(Student $student, int $limit = 5): Collection
    {
        return Student::query()
            ->with('user:id,name')
            ->get()
            ->map(fn (Student $row) => [
                'student' => $row,
                'xp' => $row->totalXp(),
            ])
            ->sortByDesc('xp')
            ->take($limit)
            ->values();
    }

    private function completedPracticeCount(Student $student): int
    {
        return DB::table('practice_sessions')
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->count();
    }

    private function completedAssignmentCount(Student $student): int
    {
        return DB::table('assignment_submissions')
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->count();
    }
}
