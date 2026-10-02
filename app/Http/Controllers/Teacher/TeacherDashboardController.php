<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\TeacherStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard guru — statistik kelas: jumlah siswa, latihan minggu ini,
 * rata-rata skor kelas, assignment aktif, monitoring singkat & analisis
 * error gesture (Phase 7).
 */
class TeacherDashboardController extends Controller
{
    public function __construct(
        private TeacherStatsService $stats,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $teacher = $this->stats->forUser($user);

        return view('teacher.dashboard', [
            'user' => $user,
            'overview' => $this->stats->classOverview($teacher),
            'students' => $this->stats->studentsOverview()->take(5),
            'activeAssignments' => $this->stats->activeAssignments($teacher),
            'errorAnalysis' => $this->stats->errorAnalysisForLesson()->take(5),
        ]);
    }
}
