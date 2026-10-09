<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard student — statistik belajar (XP, level, streak, progress),
 * aktivitas latihan terakhir, dan rekomendasi lesson (phase 3).
 */
class StudentDashboardController extends Controller
{
    public function __construct(
        private StudentStatsService $stats,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $student = $this->stats->forUser($user);

        return view('student.dashboard', [
            'user' => $user,
            'summary' => $this->stats->dashboardSummary($student),
            'progress' => $this->stats->progressByCategory($student),
            'recentSessions' => $this->stats->recentSessions($student),
            'recommended' => $this->stats->recommendedLessons($student),
        ]);
    }
}
