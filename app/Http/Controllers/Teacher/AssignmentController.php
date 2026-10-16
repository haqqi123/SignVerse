<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Lesson;
use App\Services\AssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Assignment guru (phase 6): buat tugas pada lesson tertentu,
 * bagikan ke semua student, pantau statistik pengerjaan.
 */
class AssignmentController extends Controller
{
    public function __construct(
        private AssignmentService $assignments,
    ) {}

    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;

        $assignments = Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->with(['lesson.material.category'])
            ->withCount('submissions')
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.assignments.index', [
            'assignments' => $assignments,
        ]);
    }

    public function create(): View
    {
        return view('teacher.assignments.create', [
            'lessons' => Lesson::query()
                ->with('material.category')
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lesson_id' => ['required', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'min_score' => ['required', 'integer', 'min:1', 'max:100'],
            'xp_reward' => ['required', 'integer', 'min:1', 'max:500'],
            'available_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:available_at'],
        ]);

        $assignment = $this->assignments->createAndDistribute([
            'teacher_id' => $request->user()->teacher->id,
            ...$validated,
        ]);

        return redirect()
            ->route('teacher.assignments.show', $assignment)
            ->with('status', 'Tugas dibuat & dibagikan ke semua siswa.');
    }

    public function show(Request $request, Assignment $assignment): View
    {
        abort_unless($assignment->teacher_id === $request->user()->teacher?->id, 403);

        $assignment->load([
            'lesson.material.category',
            'submissions.student.user:id,name',
        ]);

        return view('teacher.assignments.show', [
            'assignment' => $assignment,
            'stats' => $this->assignments->assignmentStats($assignment),
        ]);
    }

    public function destroy(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_unless($assignment->teacher_id === $request->user()->teacher?->id, 403);

        DB::transaction(fn () => $assignment->delete());

        return redirect()
            ->route('teacher.assignments.index')
            ->with('status', 'Tugas dihapus.');
    }
}
