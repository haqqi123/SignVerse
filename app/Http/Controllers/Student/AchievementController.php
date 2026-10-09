<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use App\Services\StudentStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Achievement student (phase 5): grid badge (terkunci/terbuka)
 * + leaderboard XP antar student.
 */
class AchievementController extends Controller
{
    public function __construct(
        private GamificationService $gamification,
        private StudentStatsService $stats,
    ) {}

    public function index(Request $request): View
    {
        $student = $this->stats->forUser($request->user());

        return view('student.achievements.index', [
            'board' => $this->gamification->achievementBoard($student),
            'summary' => $this->stats->dashboardSummary($student),
            'leaderboard' => $this->gamification->leaderboard($student),
        ]);
    }
}
