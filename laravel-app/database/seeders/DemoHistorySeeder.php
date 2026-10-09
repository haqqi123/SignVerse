<?php

namespace Database\Seeders;

use App\Models\GestureResult;
use App\Models\Lesson;
use App\Models\Material;
use App\Models\PracticeSession;
use App\Models\User;
use Illuminate\Database\Seeder;

/*
 * Paritas dengan _seed_demo_history() di signlib/db.py:
 * riwayat latihan demo realistis (RNG seed tetap) untuk akun-akun aktif,
 * lalu evaluasi badge awal — paritas _sync_badges_for_seeded_users
 * (Opsional B, Phase 1) agar achievement akun demo konsisten dengan riwayat.
 *
 * Catatan: password demo siswa tetap "siswa123" (bcrypt), sama seperti UserSeeder.
 */
class DemoHistorySeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get()->keyBy('username');
        $lessons = Lesson::all();
        if ($lessons->isEmpty()) {
            return;
        }

        $activeUsers = ['siswa@signteach.id', 'ahmad@signteach.id', 'dewi@signteach.id', 'bima@signteach.id'];
        mt_srand(42); // seed tetap seperti Python random.Random(42)
        $now = now();

        foreach ($activeUsers as $username) {
            $user = $students->get($username);
            if (! $user || PracticeSession::where('user_id', $user->id)->exists()) {
                continue;
            }

            $nDays = $username === 'siswa@signteach.id' ? random_int(5, 9) : random_int(3, 8);
            $days = (array) array_rand(array_flip(range(1, 14)), min($nDays, 14));

            foreach ($days as $dayOffset) {
                $nSessions = $username === 'siswa@signteach.id' ? random_int(2, 4) : random_int(1, 3);
                for ($s = 0; $s < $nSessions; $s++) {
                    $lesson = $lessons->random();
                    $mode = $lesson->practice_mode;
                    $target = $lesson->practice_target !== '' ? $lesson->practice_target : $lesson->target;
                    $letters = $mode === 'letter' ? [$target] : str_split(str_replace(' ', '', $target));

                    $n = count($letters);
                    $rngAcc = 62 + mt_rand() / mt_getrandmax() * 33; // 0.62..0.95
                    $correctFlags = [];
                    for ($i = 0; $i < $n; $i++) {
                        $correctFlags[] = (mt_rand() / mt_getrandmax()) < ($rngAcc / 100);
                    }
                    $correctN = count(array_filter($correctFlags));
                    $confs = [];
                    for ($i = 0; $i < $n; $i++) {
                        $confs[] = round(50 + mt_rand() / mt_getrandmax() * 47, 3) / 100;
                    }
                    $duration = 8 + mt_rand() / mt_getrandmax() * 52;

                    $createdAt = $now->copy()->subDays($dayOffset)
                        ->subHours(random_int(7, 20))->subMinutes(random_int(0, 59));

                    $acc = $correctN / max(1, $n) * 100;
                    $speed = min(100, max(40, round(100 - max(0, ($duration / $n - 2.5)) * 8, 1)));
                    $consistency = min(100, max(40, round(100 - ((max($confs) - min($confs)) * 120), 1)));
                    $completion = 100.0;
                    $finalScore = round($acc * 0.4 + $speed * 0.2 + $consistency * 0.2 + $completion * 0.2, 1);
                    $grade = $finalScore >= 90 ? 'A' : ($finalScore >= 80 ? 'B' : ($finalScore >= 70 ? 'C' : ($finalScore >= 60 ? 'D' : 'E')));

                    $session = PracticeSession::create([
                        'user_id' => $user->id,
                        'material_id' => $lesson->material_id,
                        'lesson_id' => $lesson->id,
                        'target' => $target,
                        'practice_mode' => $mode,
                        'accuracy' => round($acc, 1),
                        'speed' => $speed,
                        'consistency' => $consistency,
                        'completion' => $completion,
                        'final_score' => $finalScore,
                        'grade' => $grade,
                        'xp_earned' => 10,
                        'duration_s' => round($duration, 1),
                        'created_at' => $createdAt,
                    ]);

                    foreach ($letters as $seq => $expected) {
                        $isCorrect = $correctFlags[$seq];
                        $predicted = $isCorrect ? $letters[$seq] : $letters[array_rand($letters)];
                        GestureResult::create([
                            'session_id' => $session->id,
                            'seq' => $seq,
                            'expected' => $expected,
                            'predicted' => $predicted,
                            'confidence' => $confs[$seq],
                            'is_correct' => $isCorrect,
                            'feedback' => $isCorrect ? 'Gerakan sudah tepat' : 'Posisi tangan kurang tepat',
                            'duration_ms' => random_int(1500, 6000),
                            'created_at' => $createdAt,
                        ]);
                    }
                }
            }
        }

        // Setelah Hash di-import untuk kompatibilitas; users sudah dibuat UserSeeder.
        $this->syncBadges();
    }

    /*
     * Paritas evaluate_badges() di signlib/gamification.py + Opsional B:
     * evaluasi badge awal untuk semua siswa ber-riwayat (idempotent).
     */
    private function syncBadges(): void
    {
        $badges = \App\Models\Badge::all()->keyBy('badge_key');

        User::where('role', 'student')->whereHas('practiceSessions')->each(function (User $user) use ($badges) {
            $byCatSys = PracticeSession::query()
                ->join('materials', 'materials.id', '=', 'practice_sessions.material_id')
                ->where('practice_sessions.user_id', $user->id)
                ->groupBy('materials.category', 'materials.sign_system')
                ->selectRaw('materials.category category, materials.sign_system sign_system, COUNT(*) n, AVG(practice_sessions.accuracy) acc')
                ->get();

            $cat = fn (string $c) => $byCatSys->where('category', $c);
            $catN = fn (string $c) => (int) $cat($c)->sum('n');
            $catAcc = function (string $c) use ($cat): float {
                $rows = $cat($c);
                $n = $rows->sum('n');

                return $n ? $rows->sum(fn ($r) => $r->acc * $r->n) / $n : 0.0;
            };

            $streak = $this->streakOf($user->id);
            $challengesDone = \App\Models\ChallengeProgress::where('user_id', $user->id)->where('completed', 1)->count();
            $signCount = fn (string $s) => (int) $byCatSys->where('sign_system', $s)->sum('n');

            $checks = [
                'first_practice' => $user->practiceSessions()->count() >= 1,
                'master_alfabet' => $catN('alfabet') >= 10 && $catAcc('alfabet') >= 90,
                'master_angka' => $catN('angka') >= 10 && $catAcc('angka') >= 90,
                'sibi_explorer' => $signCount('SIBI') >= 5,
                'bisindo_explorer' => $signCount('BISINDO') >= 5,
                'streak_7' => $streak >= 7,
                'challenger' => $challengesDone >= 10,
                'perfect_round' => PracticeSession::where('user_id', $user->id)->where('accuracy', '>=', 100)->count() >= 1,
            ];

            foreach ($checks as $key => $unlocked) {
                if ($unlocked && ($badge = $badges->get($key))) {
                    \App\Models\Achievement::firstOrCreate([
                        'user_id' => $user->id,
                        'badge_id' => $badge->id,
                    ]);
                }
            }
        });
    }

    /* Paritas streak_info(): hitung hari latihan beruntun sampai hari ini/kemarin. */
    private function streakOf(int $userId): int
    {
        $days = PracticeSession::where('user_id', $userId)
            ->selectRaw('DATE(created_at) d')
            ->distinct()
            ->orderByDesc('d')
            ->pluck('d')
            ->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->toDateString());

        if ($days->isEmpty()) {
            return 0;
        }

        $today = now()->startOfDay();
        $last = \Illuminate\Support\Carbon::parse($days->first())->startOfDay();
        if (! $last->equalTo($today) && ! $last->equalTo($today->copy()->subDay())) {
            return 0;
        }

        $set = $days->flip();
        $streak = 0;
        $cursor = $last->copy();
        while ($set->has($cursor->toDateString())) {
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }
}
