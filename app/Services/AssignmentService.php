<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\PracticeSession;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Assignment (phase 6):
 *   - teacher membuat tugas pada sebuah lesson dengan deadline & syarat skor.
 *   - pembagian ke student dibuat eksplisit (baris assignment_submissions
 *     berstatus "assigned") agar guru bisa memantau siapa yang belum mengerjakan.
 *   - student menyelesaikan lewat sesi latihan gesture: skor sesi >= min_score
 *     menandakan tugas selesai dan XP reward diberikan (sekali saja).
 */
class AssignmentService
{
    public function __construct(
        private GamificationService $gamification,
    ) {}

    /**
     * Buat tugas baru + bagikan ke semua student (atau subset).
     *
     * @param  Collection<int, Student>|null  $students  Null/empty = semua student.
     */
    public function createAndDistribute(array $data, ?Collection $students = null): Assignment
    {
        return DB::transaction(function () use ($data, $students) {
            $assignment = Assignment::create($data);

            $targets = $students === null || $students->isEmpty()
                ? Student::all()
                : $students;

            foreach ($targets as $student) {
                AssignmentSubmission::firstOrCreate([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                ]);
            }

            return $assignment;
        });
    }

    /**
     * Hubungkan sesi latihan dengan tugas student yang masih "assigned":
     * skor sesi >= min_score → completed + XP; di bawah itu → tetap assigned
     * (bisa mencoba lagi).
     *
     * @return array{completed: bool, score: int, xp_earned: int, new_achievements: \Illuminate\Support\Collection}
     */
    public function submitFromPractice(Student $student, Assignment $assignment, PracticeSession $session): array
    {
        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $submission) {
            abort(404, 'Kamu tidak mendapat tugas ini.');
        }

        if ($submission->status === AssignmentSubmission::STATUS_COMPLETED) {
            return [
                'completed' => true,
                'score' => (int) $submission->score,
                'xp_earned' => (int) $submission->xp_earned,
                'new_achievements' => collect(),
            ];
        }

        $score = (int) $session->score;
        $passed = $score >= $assignment->min_score;
        $xp = $passed ? $assignment->xp_reward : 0;

        $submission->forceFill([
            'status' => $passed ? AssignmentSubmission::STATUS_COMPLETED : AssignmentSubmission::STATUS_IN_PROGRESS,
            'score' => max($score, (int) $submission->score),
            'xp_earned' => $passed ? $assignment->xp_reward : $submission->xp_earned,
            'submitted_at' => now(),
            'completed_at' => $passed ? now() : null,
        ])->save();

        $newAchievements = $passed
            ? $this->gamification->checkAchievements($student)
            : collect();

        return [
            'completed' => $passed,
            'score' => $score,
            'xp_earned' => $passed ? $assignment->xp_reward : 0,
            'new_achievements' => $newAchievements,
        ];
    }

    /**
     * Tugas milik student yang belum selesai (untuk halaman assignment student).
     */
    public function pendingFor(Student $student): Collection
    {
        return Assignment::query()
            ->whereHas('submissions', fn ($query) => $query
                ->where('student_id', $student->id)
                ->whereIn('status', [AssignmentSubmission::STATUS_ASSIGNED, AssignmentSubmission::STATUS_IN_PROGRESS]))
            ->with(['lesson.material.category', 'teacher.user', 'submissions' => fn ($q) => $q->where('student_id', $student->id)])
            ->orderBy('due_at')
            ->get();
    }

    /**
     * Tugas milik student yang sudah selesai.
     */
    public function completedFor(Student $student): Collection
    {
        return Assignment::query()
            ->whereHas('submissions', fn ($query) => $query
                ->where('student_id', $student->id)
                ->where('status', AssignmentSubmission::STATUS_COMPLETED))
            ->with(['lesson.material.category', 'teacher.user', 'submissions' => fn ($q) => $q->where('student_id', $student->id)])
            ->orderByDesc('due_at')
            ->get();
    }

    /**
     * Statistik pengerjaan satu tugas untuk guru.
     *
     * @return array{total: int, assigned: int, in_progress: int, completed: int, avg_score: float|null}
     */
    public function assignmentStats(Assignment $assignment): array
    {
        $submissions = $assignment->submissions;

        return [
            'total' => $submissions->count(),
            'assigned' => $submissions->where('status', AssignmentSubmission::STATUS_ASSIGNED)->count(),
            'in_progress' => $submissions->where('status', AssignmentSubmission::STATUS_IN_PROGRESS)->count(),
            'completed' => $submissions->where('status', AssignmentSubmission::STATUS_COMPLETED)->count(),
            'avg_score' => $submissions->where('status', AssignmentSubmission::STATUS_COMPLETED)
                ->avg('score') !== null
                ? round($submissions->where('status', AssignmentSubmission::STATUS_COMPLETED)->avg('score'), 1)
                : null,
        ];
    }
}
