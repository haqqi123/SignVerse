<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\GestureResult;
use App\Models\Lesson;
use App\Models\PracticeSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data demo practice + challenge harian untuk akun demo (dashboard guru
 * di phase 7 butuh data monitoring). Pola mengikuti project lama:
 * siswa rajin punya skor tinggi & streak, siswa lain bervariasi.
 */
class DemoPracticeSeeder extends Seeder
{
    public function run(): void
    {
        $lessons = Lesson::orderBy('id')->get();
        if ($lessons->isEmpty()) {
            return;
        }

        // --- Challenge harian: 7 hari terakhir, lesson dirotasi ---
        $challengeByDate = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $lesson = $lessons[$i % $lessons->count()];

            $challengeByDate[$date] = Challenge::create([
                'lesson_id' => $lesson->id,
                'challenge_date' => $date,
                'title' => 'Challenge Harian: '.$lesson->title,
                'description' => 'Latihan isyarat "'.$lesson->title.'" hari ini dan raih XP bonus!',
                'xp_reward' => 20,
            ]);
        }

        // --- Sesi latihan demo ---
        // [email, jumlah sesi, rata-rata skor, peluang jawaban benar]
        $profiles = [
            ['siswa@signteach.id', 12, 85, 0.85],
            ['ahmad@signteach.id', 8, 70, 0.65],
            ['dewi@signteach.id', 6, 75, 0.70],
            ['bima@signteach.id', 4, 55, 0.45],
            ['siti@signteach.id', 2, 60, 0.55],
        ];

        foreach ($profiles as [$email, $sessionCount, $avgScore, $correctRate]) {
            $user = User::where('email', $email)->first();
            if (! $user?->student) {
                continue;
            }
            $student = $user->student;

            for ($s = 0; $s < $sessionCount; $s++) {
                $lesson = $lessons[$s % $lessons->count()];
                $startedAt = now()->subDays($sessionCount - $s)->subHours(rand(1, 6));
                $score = (int) max(20, min(100, $avgScore + rand(-15, 15)));
                $attempts = rand(2, 6);
                $xp = (int) round($lesson->xp_reward * $score / 100);

                $session = PracticeSession::create([
                    'student_id' => $student->id,
                    'lesson_id' => $lesson->id,
                    'language' => $lesson->material->language,
                    'status' => PracticeSession::STATUS_COMPLETED,
                    'score' => $score,
                    'xp_earned' => $xp,
                    'attempts' => $attempts,
                    'started_at' => $startedAt,
                    'completed_at' => (clone $startedAt)->addMinutes($attempts * 2),
                ]);

                // Gesture results: satu per attempt
                for ($a = 0; $a < $attempts; $a++) {
                    $correct = (bool) (rand(1, 100) <= (int) ($correctRate * 100));
                    GestureResult::create([
                        'practice_session_id' => $session->id,
                        'lesson_id' => $lesson->id,
                        'expected_gesture' => $lesson->gesture_label,
                        'recognized_gesture' => $correct
                            ? $lesson->gesture_label
                            : $lessons[rand(0, $lessons->count() - 1)]->gesture_label,
                        'confidence' => $correct ? rand(70, 99) : rand(30, 69),
                        'correct' => $correct,
                    ]);
                }

                // Update streak student (latihan terakhir hari ini / kemarin)
                $student->forceFill([
                    'last_activity_date' => $s === $sessionCount - 1 ? today() : $student->last_activity_date,
                ])->save();
            }

            // Challenge attempts: siswa mengikuti sebagian challenge
            foreach ($challengeByDate as $date => $challenge) {
                if (rand(1, 100) > 60) {
                    continue;
                }
                $score = (int) max(20, min(100, $avgScore + rand(-10, 10)));
                $passed = $score >= 60;

                ChallengeAttempt::create([
                    'challenge_id' => $challenge->id,
                    'student_id' => $student->id,
                    'score' => $score,
                    'passed' => $passed,
                    'xp_earned' => $passed ? $challenge->xp_reward : 0,
                    'attempted_at' => $date.' 09:00:00',
                ]);
            }

            $student->refresh();
            if ($student->challengeAttempts()->where('passed', true)->exists()) {
                $student->forceFill(['current_streak' => rand(2, 9)])->save();
            }
        }
    }
}
