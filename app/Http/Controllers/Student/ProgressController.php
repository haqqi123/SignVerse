<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Progress belajar student (phase 3): persentase lesson lulus per kategori
 * + riwayat latihan lengkap.
 */
class ProgressController extends Controller
{
    public function __construct(
        private StudentStatsService $stats,
    ) {}

    public function __invoke(Request $request): View
    {
        $student = $this->stats->forUser($request->user());

        return view('student.progress', [
            'summary' => $this->stats->dashboardSummary($student),
            'progress' => $this->stats->progressByCategory($student),
            'sessions' => $this->stats->recentSessions($student, 20),
        ]);
    }
}
