<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\ChallengeService;
use App\Services\GamificationService;
use App\Services\StudentStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard student — statistik belajar (XP, level, streak, progress),
 * aktivitas latihan terakhir, rekomendasi lesson (phase 3), plus widget
 * gamifikasi: challenge harian & badge terbaru.
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
        $uid = $user->id;

        ChallengeService::ensureTodayChallenges();

        $achievements = GamificationService::achievements($uid);
        $unlockedBadges = array_values(array_filter($achievements, fn (array $badge) => $badge['unlocked']));
        $newestBadge = null;
        foreach ($unlockedBadges as $badge) {
            if ($newestBadge === null || ($badge['unlocked_at'] ?? '') > ($newestBadge['unlocked_at'] ?? '')) {
                $newestBadge = $badge;
            }
        }

        return view('student.dashboard', [
            'user' => $user,
            'summary' => $this->stats->dashboardSummary($student),
            'progress' => $this->stats->progressByCategory($student),
            'recentSessions' => $this->stats->recentSessions($student),
            'recommended' => $this->stats->recommendedLessons($student),
            'challenges' => ChallengeService::challengeStatus($uid),
            'newestBadge' => $newestBadge,
        ]);
    }
}
