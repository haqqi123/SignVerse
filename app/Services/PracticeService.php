<?php

namespace App\Services;

use App\Models\GestureResult;
use App\Models\Lesson;
use App\Models\PracticeSession;
use App\Models\Student;
use App\Services\Contracts\GestureRecognitionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Lifecycle sesi latihan (phase 4):
 * start/resume → attempt per gesture → complete (skor + XP).
 *
 * Aturan penilaian (setara project lama):
 *   - skor = persentase gesture benar dari seluruh attempt (0-100)
 *   - lulus  (>= 60) → XP penuh lesson, status completed
 *   - belum lulus → XP 0, sesi tetap completed (bisa diulang kapan saja)
 *   - satu sesi aktif (in_progress) per student; resume, bukan duplikat
 */
class PracticeService
{
    public const PASS_SCORE = 60;

    public function __construct(
        private GestureRecognitionService $recognizer,
        private GamificationService $gamification,
    ) {}

    /**
     * Mulai sesi baru untuk lesson, atau lanjutkan sesi in_progress yang ada.
     */
    public function startSession(Student $student, Lesson $lesson): PracticeSession
    {
        return PracticeSession::query()
            ->where('student_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->where('status', PracticeSession::STATUS_IN_PROGRESS)
            ->first()
            ?? PracticeSession::create([
                'student_id' => $student->id,
                'lesson_id' => $lesson->id,
                'language' => $lesson->material->language,
                'status' => PracticeSession::STATUS_IN_PROGRESS,
                'attempts' => 0,
            ]);
    }

    /**
     * Satu attempt latihan: jalankan recognizer, simpan gesture result.
     *
     * @return array{result: GestureResult, session: PracticeSession}
     */
    public function attempt(PracticeSession $session, ?string $imageData = null): array
    {
        $lesson = $session->lesson;

        $recognition = $this->recognizer->recognize($lesson->gesture_label, $imageData);

        $result = DB::transaction(function () use ($session, $lesson, $recognition) {
            $gestureResult = GestureResult::create([
                'practice_session_id' => $session->id,
                'lesson_id' => $lesson->id,
                'expected_gesture' => $lesson->gesture_label,
                'recognized_gesture' => $recognition['recognized'],
                'confidence' => $recognition['confidence'],
                'correct' => $recognition['correct'],
            ]);

            $session->increment('attempts');

            return $gestureResult;
        });

        return ['result' => $result, 'session' => $session->refresh()];
    }

    /**
     * Akhiri sesi: hitung skor dari gesture results, tentukan XP, update
     * streak student. Return sesi yang sudah final + flag lulus.
     *
     * @return array{session: PracticeSession, passed: bool, score: int, xp_earned: int}
     */
    public function complete(PracticeSession $session): array
    {
        if ($session->status !== PracticeSession::STATUS_IN_PROGRESS) {
            // Sesi sudah final — kembalikan hasilnya apa adanya (idempoten).
            return [
                'session' => $session,
                'passed' => $session->score >= self::PASS_SCORE,
                'score' => (int) $session->score,
                'xp_earned' => (int) $session->xp_earned,
            ];
        }

        return DB::transaction(function () use ($session) {
            $total = $session->gestureResults()->count();
            $correct = $session->gestureResults()->where('correct', true)->count();

            $score = $total > 0 ? (int) round($correct / $total * 100) : 0;
            $passed = $score >= self::PASS_SCORE;
            $xp = $passed ? $session->lesson->xp_reward : 0;

            $session->forceFill([
                'status' => PracticeSession::STATUS_COMPLETED,
                'score' => $score,
                'xp_earned' => $xp,
                'completed_at' => now(),
            ])->save();

            $this->updateStreak($session->student);

            // Achievement dicek setelah tiap sesi selesai (xp/streak/practice bisa berubah).
            $newAchievements = $this->gamification->checkAchievements($session->student);

            return [
                'session' => $session,
                'passed' => $passed,
                'score' => $score,
                'xp_earned' => $xp,
                'new_achievements' => $newAchievements,
            ];
        });
    }

    /**
     * Ambil sesi in_progress milik student, atau gagal 404.
     */
    public function findActiveSession(Student $student, int $sessionId): PracticeSession
    {
        $session = PracticeSession::query()
            ->where('student_id', $student->id)
            ->where('id', $sessionId)
            ->first();

        if (! $session) {
            throw (new ModelNotFoundException)->setModel(PracticeSession::class, [$sessionId]);
        }

        return $session;
    }

    /**
     * Streak: naik bila latihan hari berturut-turut, reset bila bolong,
     * rekor terpanjang ikut disimpan.
     */
    private function updateStreak(Student $student): void
    {
        $today = today();

        if ($student->last_activity_date?->isSameDay($today)) {
            return; // Sudah dihitung hari ini
        }

        $continues = $student->last_activity_date?->isSameDay($today->subDay());

        $current = $continues ? $student->current_streak + 1 : 1;

        $student->forceFill([
            'current_streak' => $current,
            'longest_streak' => max($student->longest_streak, $current),
            'last_activity_date' => $today,
        ])->save();
    }
}
