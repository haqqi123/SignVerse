<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use App\Services\StudentStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Achievement:
 * ringkasan XP/level/streak, badge diraih & terkunci dengan progress bar,
 * plus leaderboard XP dari desain SignVerse.
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
        $uid = $request->user()->id;

        $xp = GamificationService::totalXp($uid);
        $level = GamificationService::levelFromXp($xp);
        $streak = GamificationService::streakInfo($uid);
        $badges = GamificationService::achievements($uid);
        $progress = GamificationService::badgeProgress($uid);

        $unlocked = array_values(array_filter($badges, fn (array $badge) => $badge['unlocked']));
        $locked = array_values(array_filter($badges, fn (array $badge) => ! $badge['unlocked']));

        // Urutkan badge terbuka terbaru di atas.
        usort($unlocked, fn (array $a, array $b) => strcmp($b['unlocked_at'] ?? '', $a['unlocked_at'] ?? ''));

        return view('student.achievements.index', [
            'xp' => $xp,
            'level' => $level,
            'streak' => $streak,
            'badges' => $badges,
            'unlocked' => $unlocked,
            'locked' => $locked,
            'progress' => $progress,
            'leaderboard' => $this->gamification->leaderboard($student),
        ]);
    }
}
