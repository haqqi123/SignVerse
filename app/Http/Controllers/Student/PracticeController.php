<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\PracticeSession;
use App\Services\PracticeService;
use App\Services\StudentStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AI Practice (phase 4): pilih lesson → sesi latihan (kamera + attempt
 * per gesture) → hasil sesi (skor + XP).
 *
 * Attempt memakai mock recognizer; frame kamera dikirim sebagai data-URI
 * dan baru benar-benar dievaluasi AI di phase 9.
 */
class PracticeController extends Controller
{
    public function __construct(
        private PracticeService $practice,
        private StudentStatsService $stats,
    ) {}

    /**
     * Daftar lesson siap latih, dikelompokkan per kategori.
     */
    public function index(Request $request): View
    {
        $student = $this->stats->forUser($request->user());

        $categories = \App\Models\Category::query()
            ->with(['materials.lessons' => fn ($query) => $query->orderBy('order')])
            ->orderBy('order')
            ->get();

        // Skor terbaik per lesson untuk badge status di daftar.
        $bestScores = PracticeSession::query()
            ->where('student_id', $student->id)
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->selectRaw('lesson_id, MAX(score) as best_score')
            ->groupBy('lesson_id')
            ->pluck('best_score', 'lesson_id');

        return view('student.practice.index', [
            'categories' => $categories,
            'bestScores' => $bestScores,
        ]);
    }

    /**
     * Mulai (atau resume) sesi latihan untuk sebuah lesson.
     */
    public function start(Request $request, Lesson $lesson): RedirectResponse
    {
        $student = $this->stats->forUser($request->user());

        $session = $this->practice->startSession($student, $lesson);

        return redirect()
            ->route('student.practice.show', $session)
            ->with('status', 'Sesi latihan dimulai — praktikkan isyarat "'.$lesson->title.'"!');
    }

    /**
     * Halaman latihan aktif: kamera + tombol ambil attempt + akhiri sesi.
     */
    public function show(Request $request, PracticeSession $session): View
    {
        abort_unless($session->student_id === $request->user()->student?->id, 403);

        $session->load('lesson.material.category');

        return view('student.practice.show', [
            'session' => $session,
            'results' => $session->gestureResults()->latest('id')->limit(10)->get(),
        ]);
    }

    /**
     * Simpan satu attempt dari frame kamera (AJAX, returns JSON).
     */
    public function attempt(Request $request, PracticeSession $session): \Illuminate\Http\JsonResponse
    {
        abort_unless($session->student_id === $request->user()->student?->id, 403);
        abort_unless($session->status === PracticeSession::STATUS_IN_PROGRESS, 422, 'Sesi sudah selesai.');

        $validated = $request->validate([
            'image' => ['nullable', 'string'],
        ]);

        $payload = $this->practice->attempt($session, $validated['image'] ?? null);

        return response()->json([
            'correct' => $payload['result']->correct,
            'recognized' => $payload['result']->recognized_gesture,
            'expected' => $payload['result']->expected_gesture,
            'confidence' => $payload['result']->confidence,
            'attempts' => $payload['session']->attempts,
        ]);
    }

    /**
     * Akhiri sesi dan tampilkan hasil (skor + XP + streak).
     */
    public function complete(Request $request, PracticeSession $session): RedirectResponse
    {
        abort_unless($session->student_id === $request->user()->student?->id, 403);

        $outcome = $this->practice->complete($session);

        $message = $outcome['passed']
            ? 'Hebat! Kamu lulus dengan skor '.$outcome['score'].' (+'.$outcome['xp_earned'].' XP).'
            : 'Belum lulus (skor '.$outcome['score'].'). Coba lagi ya!';

        if ($outcome['new_achievements']->isNotEmpty()) {
            $names = $outcome['new_achievements']->pluck('name')->implode(', ');
            $message .= ' 🏆 Achievement baru: '.$names.'!';
        }

        return redirect()
            ->route('student.practice.result', $session)
            ->with($outcome['passed'] ? 'status' : 'error', $message);
    }

    /**
     * Halaman hasil sesi.
     */
    public function result(Request $request, PracticeSession $session): View
    {
        abort_unless($session->student_id === $request->user()->student?->id, 403);

        $session->load('lesson.material.category');

        return view('student.practice.result', [
            'session' => $session,
            'passed' => $session->score >= PracticeService::PASS_SCORE,
        ]);
    }
}
