<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\GestureResult;
use App\Models\Lesson;
use App\Models\PracticeSession;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data demo practice + challenge harian untuk akun demo (dashboard guru
 * di phase 7 butuh data monitoring). Mengikuti data-model gamifikasi:
 * challenge harian per kategori (tanggal + kategori unik) dan progres user
 * di challenge_progress.
 */
class DemoPracticeSeeder extends Seeder
{
    public function run(): void
    {
        $lessons = Lesson::orderBy('id')->get();
        if ($lessons->isEmpty()) {
            return;
        }

        // --- Challenge harian: 7 hari terakhir, 2 kategori per hari ---
        $challengeByDate = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();

            $challengeByDate[$date] = [
                Challenge::updateOrCreate(
                    ['challenge_date' => $date, 'category' => 'alfabet'],
                    ['title' => 'Praktik 5 huruf', 'description' => 'Lakukan 5 latihan huruf alfabet hari ini.', 'target' => 5, 'reward_xp' => 20],
                ),
                Challenge::updateOrCreate(
                    ['challenge_date' => $date, 'category' => 'kosakata'],
                    ['title' => 'Praktik 3 kosakata', 'description' => 'Lakukan 3 latihan kata kosakata hari ini.', 'target' => 3, 'reward_xp' => 20],
                ),
            ];
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

            for ($s = 0; $s < $sessionCount; $s++) {
                $lesson = $lessons[$s % $lessons->count()];
                $startedAt = now()->subDays($sessionCount - $s)->subHours(rand(1, 6));
                $score = (int) max(20, min(100, $avgScore + rand(-15, 15)));
                $attempts = rand(2, 6);
                // Base XP: 10 + bonus 10 jika akurasi >= 90.
                $xp = $score >= 90 ? 20 : 10;

                $session = PracticeSession::create([
                    'student_id' => $user->student->id,
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
            }

            // Challenge progress: siswa mengikuti sebagian challenge harian
            foreach ($challengeByDate as $challengePair) {
                foreach ($challengePair as $challenge) {
                    if (rand(1, 100) > 60) {
                        continue;
                    }

                    $completed = rand(1, 100) <= 40;
                    $progress = $completed ? $challenge->target : rand(0, $challenge->target - 1);

                    \DB::table('challenge_progress')->updateOrInsert(
                        ['user_id' => $user->id, 'challenge_id' => $challenge->id],
                        [
                            'progress' => $progress,
                            'completed' => $completed,
                            'completed_at' => $completed
                                ? \Carbon\Carbon::parse($challenge->challenge_date)->setTime(9, 0)
                                : null,
                        ],
                    );
                }
            }
        }
    }
}
