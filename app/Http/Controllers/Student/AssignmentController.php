<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\PracticeSession;
use App\Services\AssignmentService;
use App\Services\StudentStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Assignment student (phase 6): daftar tugas (pending/selesai), detail tugas,
 * dan penyelesaian lewat sesi latihan gesture (skor >= min_score).
 */
class AssignmentController extends Controller
{
    public function __construct(
        private AssignmentService $assignments,
        private StudentStatsService $stats,
    ) {}

    public function index(Request $request): View
    {
        $student = $this->stats->forUser($request->user());

        return view('student.assignments.index', [
            'pending' => $this->assignments->pendingFor($student),
            'completed' => $this->assignments->completedFor($student),
        ]);
    }

    public function show(Request $request, Assignment $assignment): View
    {
        $student = $this->stats->forUser($request->user());

        $submission = $assignment->submissions()
            ->where('student_id', $student->id)
            ->firstOrFail();

        $assignment->load('lesson.material.category', 'teacher.user');

        return view('student.assignments.show', [
            'assignment' => $assignment,
            'submission' => $submission,
        ]);
    }

    /**
     * Selesaikan tugas memakai sesi latihan yang sudah final (completed).
     * Skor sesi dibandingkan dengan min_score tugas.
     */
    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        $student = $this->stats->forUser($request->user());

        $validated = $request->validate([
            'practice_session_id' => ['required', 'integer'],
        ]);

        $session = PracticeSession::query()
            ->where('student_id', $student->id)
            ->where('id', $validated['practice_session_id'])
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->first();

        if (! $session) {
            return back()->with('error',
                'Sesi latihan tidak ditemukan / belum selesai. Selesaikan latihan dulu di halaman practice.');
        }

        $outcome = $this->assignments->submitFromPractice($student, $assignment, $session);

        if ($outcome['completed'] && $outcome['new_achievements']->isNotEmpty()) {
            $names = $outcome['new_achievements']->pluck('name')->implode(', ');

            return redirect()
                ->route('student.assignments.show', $assignment)
                ->with('status', '🎉 Tugas selesai! Skor '.$outcome['score'].' (+'.$outcome['xp_earned'].' XP). Achievement baru: '.$names.'!');
        }

        if ($outcome['completed']) {
            return redirect()
                ->route('student.assignments.show', $assignment)
                ->with('status', '🎉 Tugas selesai! Skor '.$outcome['score'].' (+'.$outcome['xp_earned'].' XP).');
        }

        return redirect()
            ->route('student.assignments.show', $assignment)
            ->with('error', 'Skor latihan '.$outcome['score'].' belum mencapai syarat '.$assignment->min_score.'. Latihan lagi lalu kumpulkan ulang!');
    }
}
