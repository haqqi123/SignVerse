<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\PracticeSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Statistik dashboard & progress student (phase 3).
 *
 * XP dan level dihitung agregat dari practice_sessions + challenge/assignment
 * rewards (tanpa kolom xp di students — desain sama dengan project lama).
 */
class StudentStatsService
{
    /**
     * Ringkasan lengkap untuk dashboard student.
     *
     * @return array{xp: int, level: int, xp_in_level: int, xp_for_next_level: int, streak: int, longest_streak: int, sessions: int, avg_score: float, lessons_mastered: int, lessons_total: int}
     */
    public function dashboardSummary(Student $student): array
    {
        $xp = $student->totalXp();
        $level = $student->level();
        $xpInLevel = $xp - (($level - 1) * 100);

        $sessions = PracticeSession::query()
            ->where('student_id', $student->id)
            ->where('status', PracticeSession::STATUS_COMPLETED);

        $avgScore = (float) (clone $sessions)->avg('score');

        return [
            'xp' => $xp,
            'level' => $level,
            'xp_in_level' => $xpInLevel,
            'xp_for_next_level' => 100 - $xpInLevel,
            'streak' => $student->current_streak,
            'longest_streak' => $student->longest_streak,
            'sessions' => (clone $sessions)->count(),
            'avg_score' => round($avgScore, 1),
            'lessons_mastered' => $this->lessonsMastered($student),
            'lessons_total' => (int) Lesson::count(),
        ];
    }

    /**
     * Sesi latihan terakhir untuk aktivitas di dashboard.
     */
    public function recentSessions(Student $student, int $limit = 5): Collection
    {
        return PracticeSession::query()
            ->where('student_id', $student->id)
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->with(['lesson.material.category'])
            ->latest('completed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Progress per kategori: persentase lesson yang pernah dikerjakan
     * dengan skor lulus (>= 60).
     *
     * @return Collection<int, array{category: Category, total: int, done: int, percent: int}>
     */
    public function progressByCategory(Student $student): Collection
    {
        $lessonIdsByCategory = Category::query()
            ->with(['materials.lessons:id,material_id'])
            ->get()
            ->mapWithKeys(fn (Category $category) => [
                $category->id => $category->materials
                    ->flatMap(fn ($material) => $material->lessons->pluck('id'))
                    ->unique()
                    ->values(),
            ]);

        $passedLessonIds = PracticeSession::query()
            ->where('student_id', $student->id)
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->where('score', '>=', 60)
            ->pluck('lesson_id')
            ->unique();

        return Category::query()
            ->orderBy('order')
            ->get()
            ->map(function (Category $category) use ($lessonIdsByCategory, $passedLessonIds) {
                $lessonIds = $lessonIdsByCategory->get($category->id, collect());
                $total = $lessonIds->count();
                $done = $lessonIds->intersect($passedLessonIds)->count();

                return [
                    'category' => $category,
                    'total' => $total,
                    'done' => $done,
                    'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
                ];
            });
    }

    /**
     * Jumlah lesson yang pernah dilatih dengan skor lulus.
     */
    public function lessonsMastered(Student $student): int
    {
        return PracticeSession::query()
            ->where('student_id', $student->id)
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->where('score', '>=', 60)
            ->distinct('lesson_id')
            ->count('lesson_id');
    }

    /**
     * Rekomendasi lesson untuk dilatih berikutnya:
     * prioritas yang pernah dilatih tapi belum lulus, lalu yang belum dilatih.
     */
    public function recommendedLessons(Student $student, int $limit = 3): Collection
    {
        $scores = PracticeSession::query()
            ->where('student_id', $student->id)
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->selectRaw('lesson_id, MAX(score) as best_score')
            ->groupBy('lesson_id')
            ->pluck('best_score', 'lesson_id');

        $attemptedFailing = Lesson::query()
            ->whereIn('id', $scores->filter(fn ($score) => $score < 60)->keys())
            ->with('material.category')
            ->orderBy('difficulty')
            ->limit($limit)
            ->get();

        if ($attemptedFailing->count() >= $limit) {
            return $attemptedFailing;
        }

        $fresh = Lesson::query()
            ->whereNotIn('id', $scores->keys())
            ->with('material.category')
            ->orderBy('difficulty')
            ->orderBy('order')
            ->limit($limit - $attemptedFailing->count())
            ->get();

        return $attemptedFailing->concat($fresh);
    }

    /**
     * Student model dari user yang sedang login.
     * Profil dibuat otomatis jika belum ada (safety net untuk user lama).
     */
    public function forUser(User $user): Student
    {
        return $user->student ?? $user->student()->create();
    }
}
