<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use App\Services\StudentStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Challenge harian (phase 5): satu lesson spesial per hari, XP bonus,
 * sekali attempt per student.
 */
class ChallengeController extends Controller
{
    public function __construct(
        private GamificationService $gamification,
        private StudentStatsService $stats,
    ) {}

    public function show(Request $request): View
    {
        $student = $this->stats->forUser($request->user());
        $challenge = $this->gamification->todayChallenge();
        $challenge->load('lesson.material.category');

        $attempt = $challenge->attempts()->where('student_id', $student->id)->first();

        return view('student.challenge.show', [
            'challenge' => $challenge,
            'attempt' => $attempt,
        ]);
    }

    public function attempt(Request $request): RedirectResponse
    {
        $student = $this->stats->forUser($request->user());
        $challenge = $this->gamification->todayChallenge();

        $outcome = $this->gamification->attemptChallenge($student, $challenge);

        if ($outcome['already_attempted']) {
            return redirect()
                ->route('student.challenge.show')
                ->with('error', 'Kamu sudah mengikuti challenge hari ini (skor '.$outcome['score'].').');
        }

        $newAchievements = $this->gamification->checkAchievements($student);

        $message = $outcome['passed']
            ? '🎉 Challenge selesai! Skor '.$outcome['score'].' (+'.$outcome['xp_earned'].' XP bonus).'
            : 'Challenge belum lulus (skor '.$outcome['score'].'). Coba lagi besok!';

        if ($newAchievements->isNotEmpty()) {
            $message .= ' 🏆 Achievement baru: '.$newAchievements->pluck('name')->implode(', ').'!';
        }

        return redirect()
            ->route('student.challenge.show')
            ->with($outcome['passed'] ? 'status' : 'error', $message);
    }
}
