<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\ChallengeService;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Challenge harian:
 * daftar tantangan per kategori hari ini + streak + riwayat reward.
 * Progres naik otomatis ketika student menyelesaikan latihan (PracticeService).
 */
class ChallengeController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $uid = $user->id;

        // Challenge hari ini dibuat (idempoten) sebelum halaman dirender.
        ChallengeService::ensureTodayChallenges();
        $challenges = ChallengeService::challengeStatus($uid);
        $streak = GamificationService::streakInfo($uid);
        $rewards = ChallengeService::challengeRewards($uid);

        return view('student.challenge.show', [
            'challenges' => $challenges,
            'streak' => $streak['streak'],
            'isToday' => $streak['is_today'],
            'rewards' => $rewards,
        ]);
    }
}
