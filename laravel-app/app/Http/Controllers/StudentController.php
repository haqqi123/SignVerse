<?php

namespace App\Http\Controllers;

use App\Models\GestureResult;
use App\Models\PracticeSession;
use App\Services\AssignmentService;
use App\Services\ChallengeService;
use App\Services\GamificationService;
use App\Services\LearningService;
use App\Services\ScoringService;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentController extends Controller
{
    /** Dasbor utama siswa (ringkasan belajar, rekomendasi, continue learning, tantangan). */
    public function dashboard(): View
    {
        $user = auth()->user();
        $uid = $user->id;

        $stats = StudentService::studentStats($uid);
        $streak = GamificationService::streakInfo($uid);
        $xp = GamificationService::totalXp($uid);
        $level = GamificationService::levelFromXp($xp);
        $last = LearningService::lastLesson($uid);
        $recommend = StudentService::recommendation($uid);
        $challenges = ChallengeService::challengeStatus($uid);
        $achievements = GamificationService::achievements($uid);
        $unlockedBadges = array_values(array_filter($achievements, fn ($b) => $b['unlocked']));

        return view('student.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'streak' => $streak,
            'xp' => $xp,
            'level' => $level,
            'last' => $last,
            'recommend' => $recommend,
            'challenges' => $challenges,
            'newestBadge' => $unlockedBadges[0] ?? null,
        ]);
    }

    /** Katalog materi belajar (Alfabet, Angka, Kosakata; SIBI & BISINDO). */
    public function materials(Request $request): View
    {
        $uid = auth()->id();
        $activeCategory = $request->query('category');
        $activeSystem = $request->query('system');

        $materials = LearningService::getMaterials();
        if ($activeCategory) {
            $materials = $materials->where('category', $activeCategory);
        }
        if ($activeSystem) {
            $materials = $materials->where('sign_system', $activeSystem);
        }

        $progress = LearningService::materialProgress($uid);

        return view('student.materials', [
            'materials' => $materials,
            'progress' => $progress,
            'activeCategory' => $activeCategory,
            'activeSystem' => $activeSystem,
        ]);
    }

    /** Halaman ruang latihan AI (Pemilihan lesson atau sesi kamera langsung). */
    public function practice(Request $request): View
    {
        $uid = auth()->id();
        $lessonId = $request->query('lesson_id');

        $lesson = null;
        if ($lessonId) {
            $lesson = LearningService::getLesson((int) $lessonId);
        }

        $stats = StudentService::studentStats($uid);
        $streak = GamificationService::streakInfo($uid);
        $xp = GamificationService::totalXp($uid);
        $level = GamificationService::levelFromXp($xp);
        $materials = LearningService::getMaterials();
        $recommend = StudentService::recommendation($uid);
        $challenges = ChallengeService::challengeStatus($uid);

        return view('student.practice', [
            'lesson' => $lesson,
            'stats' => $stats,
            'streak' => $streak,
            'xp' => $xp,
            'level' => $level,
            'materials' => $materials,
            'recommend' => $recommend,
            'challenges' => $challenges,
        ]);
    }

    /** Submit hasil sesi latihan dari kamera frontend untuk dinilai Smart Assessment. */
    public function submitPractice(Request $request): JsonResponse
    {
        $user = auth()->user();
        $uid = $user->id;

        $validated = $request->validate([
            'lesson_id' => ['required', 'integer', 'exists:lessons,id'],
            'letters' => ['required', 'array', 'min:1'],
            'captures' => ['required', 'array'],
            'confidences' => ['required', 'array'],
            'durations_s' => ['required', 'array'],
            'wrong_count' => ['nullable', 'integer', 'min:0'],
            'wrong_attempts' => ['nullable', 'array'],
        ]);

        $lesson = LearningService::getLesson($validated['lesson_id']);
        $materialId = $lesson->material_id;

        $wrongCount = (int) ($validated['wrong_count'] ?? 0);
        $assessment = ScoringService::computeAssessment(
            $validated['letters'],
            $validated['captures'],
            $validated['confidences'],
            $validated['durations_s'],
            $wrongCount,
        );

        $recommendationText = ScoringService::recommendation($assessment);
        $assessment['recommendation'] = $recommendationText;

        // Base XP: 10 + bonus 10 jika Grade A
        $xpEarned = 10;
        if ($assessment['accuracy'] >= 90) {
            $xpEarned += 10;
        }

        $totalDuration = array_sum($validated['durations_s']);

        // Catat sesi latihan
        $session = PracticeSession::create([
            'user_id' => $uid,
            'material_id' => $materialId,
            'lesson_id' => $lesson->id,
            'target' => $lesson->practice_target ?: $lesson->target,
            'practice_mode' => $lesson->practice_mode,
            'accuracy' => $assessment['accuracy'],
            'speed' => $assessment['speed'],
            'consistency' => $assessment['consistency'],
            'completion' => $assessment['completion'],
            'final_score' => $assessment['final'],
            'grade' => $assessment['grade'],
            'xp_earned' => $xpEarned,
            'duration_s' => round($totalDuration, 1),
        ]);

        // Catat gesture results
        $seq = 0;
        foreach ($validated['captures'] as $i => $captured) {
            $expected = $validated['letters'][$i] ?? $captured;
            $conf = (float) ($validated['confidences'][$i] ?? 0.8);
            $durMs = (int) ((float) ($validated['durations_s'][$i] ?? 1.0) * 1000);
            $feedback = ScoringService::feedbackFor($expected, $captured, $conf);

            GestureResult::create([
                'session_id' => $session->id,
                'seq' => ++$seq,
                'expected' => $expected,
                'predicted' => $captured,
                'confidence' => $conf,
                'is_correct' => ($expected === $captured),
                'feedback' => $feedback,
                'duration_ms' => $durMs,
            ]);
        }

        // Catat wrong attempts jika ada
        if (! empty($validated['wrong_attempts'])) {
            foreach ($validated['wrong_attempts'] as $wa) {
                $exp = $wa['expected'] ?? '-';
                $pred = $wa['predicted'] ?? '-';
                $conf = (float) ($wa['confidence'] ?? 0.6);
                GestureResult::create([
                    'session_id' => $session->id,
                    'seq' => ++$seq,
                    'expected' => $exp,
                    'predicted' => $pred,
                    'confidence' => $conf,
                    'is_correct' => false,
                    'feedback' => ScoringService::feedbackFor($exp, $pred, $conf),
                    'duration_ms' => 0,
                ]);
            }
        }

        // Evaluasi Gamifikasi (Badge unlock)
        $badgesBefore = GamificationService::unlockedKeys($uid);
        GamificationService::evaluateBadges($uid);
        $badgesAfter = GamificationService::unlockedKeys($uid);
        $newBadges = array_values(array_diff($badgesAfter, $badgesBefore));

        // Tambah progres tantangan harian
        if ($lesson->material) {
            ChallengeService::bumpChallenge($uid, $lesson->material->category);
        }

        // Auto-complete assignment yang relevan
        $completedAssignments = AssignmentService::completeAssignmentsForMaterial($uid, $materialId);

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'assessment' => $assessment,
            'xp_earned' => $xpEarned,
            'new_badges' => $newBadges,
            'completed_assignments' => $completedAssignments,
        ]);
    }

    /** Ruang Komunikasi Inklusif (Sign-to-Text & Speech-to-Sign). */
    public function inclusive(): View
    {
        return view('student.inclusive');
    }

    /** Halaman achievement (badge diraih & badge terkunci dengan progress bar). */
    public function achievements(): View
    {
        $uid = auth()->id();

        $xp = GamificationService::totalXp($uid);
        $level = GamificationService::levelFromXp($xp);
        $streak = GamificationService::streakInfo($uid);
        $badges = GamificationService::achievements($uid);
        $progress = GamificationService::badgeProgress($uid);

        $unlocked = array_values(array_filter($badges, fn ($b) => $b['unlocked']));
        $locked = array_values(array_filter($badges, fn ($b) => ! $b['unlocked']));

        // Urutkan badge terbuka terbaru di atas
        usort($unlocked, fn ($a, $b) => strcmp($b['unlocked_at'] ?? '', $a['unlocked_at'] ?? ''));

        return view('student.achievements', [
            'xp' => $xp,
            'level' => $level,
            'streak' => $streak,
            'badges' => $badges,
            'unlocked' => $unlocked,
            'locked' => $locked,
            'progress' => $progress,
        ]);
    }

    /** Halaman tantangan harian (Daily Challenges). */
    public function challenge(): View
    {
        $uid = auth()->id();

        $challenges = ChallengeService::challengeStatus($uid);
        $streak = GamificationService::streakInfo($uid);
        $rewards = ChallengeService::challengeRewards($uid);

        return view('student.challenge', [
            'challenges' => $challenges,
            'streak' => $streak,
            'rewards' => $rewards,
        ]);
    }

    /** Halaman riwayat progres & grafik tren akurasi. */
    public function progress(): View
    {
        $uid = auth()->id();

        $stats = StudentService::studentStats($uid);
        $streak = GamificationService::streakInfo($uid);
        $xp = GamificationService::totalXp($uid);
        $level = GamificationService::levelFromXp($xp);
        $categoryAccuracy = StudentService::categoryAccuracy($uid);
        $trend = StudentService::accuracyTrend($uid, 7);
        $history = StudentService::practiceHistory($uid, 25);
        $errors = StudentService::gestureErrors($uid, 5);

        return view('student.progress', [
            'stats' => $stats,
            'streak' => $streak,
            'xp' => $xp,
            'level' => $level,
            'categoryAccuracy' => $categoryAccuracy,
            'trend' => $trend,
            'history' => $history,
            'gestureErrors' => $errors,
        ]);
    }

    /** Halaman profil siswa. */
    public function profile(): View
    {
        $user = auth()->user();
        $uid = $user->id;

        $stats = StudentService::studentStats($uid);
        $xp = GamificationService::totalXp($uid);
        $level = GamificationService::levelFromXp($xp);
        $streak = GamificationService::streakInfo($uid);

        return view('student.profile', [
            'user' => $user,
            'stats' => $stats,
            'xp' => $xp,
            'level' => $level,
            'streak' => $streak,
        ]);
    }

    /** Perbarui data profil siswa (nama & password). */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /** Halaman daftar tugas (Assignment) siswa. */
    public function assignment(): View
    {
        $uid = auth()->id();
        $assignments = AssignmentService::studentAssignments($uid);

        $counts = [
            'total' => $assignments->count(),
            'belum_dimulai' => $assignments->where('status', 'belum dimulai')->count(),
            'sedang_dikerjakan' => $assignments->where('status', 'sedang dikerjakan')->count(),
            'selesai' => $assignments->where('status', 'selesai')->count(),
        ];

        return view('student.assignment', [
            'assignments' => $assignments,
            'counts' => $counts,
        ]);
    }

    /** Mulai kerjakan tugas: set status ke sedang dikerjakan dan redirect ke latihan materi. */
    public function startAssignment(int $id): RedirectResponse
    {
        $uid = auth()->id();
        $assignments = AssignmentService::studentAssignments($uid);
        $assignment = $assignments->firstWhere('assignment_id', $id);

        if (! $assignment) {
            return back()->with('error', 'Tugas tidak ditemukan.');
        }

        if ($assignment->status === 'belum dimulai') {
            AssignmentService::updateAssignmentStatus($uid, $id, 'sedang dikerjakan');
        }

        $firstLesson = $assignment->material_id ? LearningService::firstLesson($assignment->material_id) : null;
        if ($firstLesson) {
            return redirect()->route('student.practice', ['lesson_id' => $firstLesson->id]);
        }

        return redirect()->route('student.practice');
    }

    /** Selesaikan tugas secara manual oleh siswa. */
    public function completeAssignment(int $id): RedirectResponse
    {
        $uid = auth()->id();
        AssignmentService::updateAssignmentStatus($uid, $id, 'selesai');

        return back()->with('success', 'Tugas berhasil diselesaikan!');
    }
}
