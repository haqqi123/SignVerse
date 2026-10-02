<?php

namespace App\Services;

use App\Models\GestureResult;
use App\Models\PracticeSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Teacher module (phase 7): statistik kelas, monitoring siswa,
 * dan analisis error gesture dari gesture_results.
 *
 * Semua angka agregat dihitung dari practice_sessions (completed),
 * assignment_submissions, dan gesture_results — tanpa kolom denormalisasi.
 */
class TeacherStatsService
{
    /* ==========================================================================
     | Ringkasan kelas (dashboard)
     * ========================================================================*/

    /**
     * Ringkasan kelas untuk dashboard guru.
     *
     * @return array{students: int, weekly_sessions: int, avg_class_score: float, active_assignments: int}
     */
    public function classOverview(Teacher $teacher): array
    {
        $completed = PracticeSession::query()
            ->where('status', PracticeSession::STATUS_COMPLETED);

        return [
            'students' => Student::count(),
            'weekly_sessions' => (int) (clone $completed)
                ->where('completed_at', '>=', now()->startOfWeek())
                ->count(),
            'avg_class_score' => round((float) (clone $completed)->avg('score'), 1),
            'active_assignments' => $this->activeAssignments($teacher)->count(),
        ];
    }

    /**
     * Assignment milik guru yang belum melewati deadline (atau tanpa deadline).
     */
    public function activeAssignments(Teacher $teacher): Collection
    {
        return $teacher->assignments()
            ->with('lesson.material.category')
            ->where(fn ($query) => $query
                ->whereNull('due_at')
                ->orWhere('due_at', '>=', now()))
            // Nulls-last: deadline terdekat lebih dulu, tanpa deadline di akhir
            // (berlaku di MySQL maupun SQLite).
            ->orderByRaw('due_at IS NULL, due_at')
            ->get();
    }

    /* ==========================================================================
     | Monitoring per siswa
     * ========================================================================*/

    /**
     * Semua siswa dengan ringkasan aktivitas untuk tabel monitoring.
     *
     * @return Collection<int, array{student: Student, name: string, sessions: int, avg_score: float, xp: int, level: int, streak: int, last_activity: \Illuminate\Support\Carbon|null}>
     */
    public function studentsOverview(): Collection
    {
        return Student::query()
            ->with('user:id,name')
            ->get()
            ->map(fn (Student $student) => $this->studentSummary($student))
            ->sortByDesc('sessions')
            ->values();
    }

    /**
     * Ringkasan satu siswa (dipakai di tabel monitoring & halaman detail).
     *
     * @return array{student: Student, name: string, sessions: int, avg_score: float, xp: int, level: int, streak: int, last_activity: \Illuminate\Support\Carbon|null}
     */
    public function studentSummary(Student $student): array
    {
        $completed = PracticeSession::query()
            ->where('student_id', $student->id)
            ->where('status', PracticeSession::STATUS_COMPLETED);

        return [
            'student' => $student,
            'name' => $student->user?->name ?? 'Siswa #'.$student->id,
            'sessions' => (int) (clone $completed)->count(),
            'avg_score' => round((float) (clone $completed)->avg('score'), 1),
            'xp' => $student->totalXp(),
            'level' => $student->level(),
            'streak' => $student->current_streak,
            'last_activity' => $student->last_activity_date,
        ];
    }

    /**
     * Detail lengkap satu siswa untuk halaman monitoring detail.
     *
     * @return array{
     *     summary: array, recent_sessions: Collection, category_progress: Collection, achievements: Collection
     * }
     */
    public function studentDetail(Student $student): array
    {
        $recentSessions = PracticeSession::query()
            ->where('student_id', $student->id)
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->with(['lesson.material.category'])
            ->latest('completed_at')
            ->limit(5)
            ->get();

        return [
            'summary' => $this->studentSummary($student),
            'recent_sessions' => $recentSessions,
            'category_progress' => app(StudentStatsService::class)->progressByCategory($student),
            'achievements' => $student->achievements()->orderByPivot('unlocked_at', 'desc')->get(),
        ];
    }

    /**
     * Teacher model dari user yang sedang login.
     * Profil dibuat otomatis jika belum ada (safety net untuk user lama).
     */
    public function forUser(User $user): Teacher
    {
        return $user->teacher ?? $user->teacher()->create();
    }

    /* ==========================================================================
     | Analisis error
     * ========================================================================*/

    /**
     * Analisis error: gesture salah terbanyak (opsional per lesson).
     *
     * @return Collection<int, array{lesson_id: int, lesson: string, gesture: string, wrong: int, total: int, accuracy: int}>
     */
    public function errorAnalysisForLesson(?int $lessonId = null): Collection
    {
        $results = GestureResult::query()
            ->when($lessonId !== null, fn ($query) => $query->where('lesson_id', $lessonId))
            ->with('lesson:id,title')
            ->get();

        return $results
            ->groupBy(fn (GestureResult $result) => $result->lesson_id.'|'.$result->expected_gesture)
            ->map(fn (Collection $group) => [
                'lesson_id' => $group->first()->lesson_id,
                'lesson' => $group->first()->lesson?->title ?? 'Lesson #'.$group->first()->lesson_id,
                'gesture' => $group->first()->expected_gesture,
                'wrong' => $group->where('correct', false)->count(),
                'total' => $group->count(),
            ])
            ->map(function (array $row) {
                $row['accuracy'] = $row['total'] > 0
                    ? (int) round(($row['total'] - $row['wrong']) / $row['total'] * 100)
                    : 0;

                return $row;
            })
            ->filter(fn (array $row) => $row['wrong'] > 0)
            ->sortByDesc('wrong')
            ->take(10)
            ->values();
    }

    /**
     * Analisis error untuk satu siswa: gesture yang sering salah oleh siswa tsb.
     *
     * @return Collection<int, array{lesson_id: int, lesson: string, gesture: string, wrong: int, total: int, accuracy: int}>
     */
    public function errorAnalysisForStudent(Student $student): Collection
    {
        $results = GestureResult::query()
            ->whereHas('practiceSession', fn ($query) => $query->where('student_id', $student->id))
            ->with('lesson:id,title')
            ->get();

        return $results
            ->groupBy('expected_gesture')
            ->map(fn (Collection $group) => [
                'lesson_id' => $group->first()->lesson_id,
                'lesson' => $group->first()->lesson?->title ?? 'Lesson #'.$group->first()->lesson_id,
                'gesture' => $group->first()->expected_gesture,
                'wrong' => $group->where('correct', false)->count(),
                'total' => $group->count(),
            ])
            ->map(function (array $row) {
                $row['accuracy'] = $row['total'] > 0
                    ? (int) round(($row['total'] - $row['wrong']) / $row['total'] * 100)
                    : 0;

                return $row;
            })
            ->filter(fn (array $row) => $row['wrong'] > 0)
            ->sortByDesc('wrong')
            ->take(10)
            ->values();
    }

    /**
     * Akurasi rata-rata recognition (persentase gesture benar) per lesson
     * — untuk widget keseluruhan analisis error.
     *
     * @return Collection<int, array{lesson_id: int, lesson: string, total: int, correct: int, accuracy: int}>
     */
    public function lessonAccuracy(): Collection
    {
        return GestureResult::query()
            ->with('lesson:id,title')
            ->get()
            ->groupBy('lesson_id')
            ->map(fn (Collection $group, int $lessonId) => [
                'lesson_id' => $lessonId,
                'lesson' => $group->first()->lesson?->title ?? 'Lesson #'.$lessonId,
                'total' => $group->count(),
                'correct' => $group->where('correct', true)->count(),
            ])
            ->map(function (array $row) {
                $row['accuracy'] = $row['total'] > 0
                    ? (int) round($row['correct'] / $row['total'] * 100)
                    : 0;

                return $row;
            })
            ->sortBy('accuracy')
            ->values();
    }
}
