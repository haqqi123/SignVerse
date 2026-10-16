<?php

namespace App\Services;

use App\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/*
 * Gamifikasi SignVerse (phase 5): XP, level bernama, streak, dan badge ber-key.
 *
 * Adaptasi skema SignVerse:
 *   - practice_sessions ber-key student_id (via students.user_id), metrik
 *     akurasi = kolom `score` (0-100), sesi dihitung yang status completed.
 *   - kategori  : categories.slug ('alphabet','number','word','phrase').
 *   - sistem    : materials.language enum ('sibi','bisindo').
 *   - XP challenge = challenge_progress completed × reward_xp; XP assignment
 *     tetap dihitung karena modul assignment SignVerse memberi XP reward.
 */
class GamificationService
{
    /** [threshold_xp, nama_level] */
    private const LEVELS = [
        [0, 'Beginner'],
        [300, 'Explorer'],
        [900, 'Communicator'],
        [2000, 'Inclusive Champion'],
    ];

    private const LEVEL_COLORS = [
        'Beginner' => '#94a3b8',
        'Explorer' => '#6366f1',
        'Communicator' => '#06b6d4',
        'Inclusive Champion' => '#f59e0b',
    ];

    /** Target progres tiap badge (tampilan "x/target" pada badge locked). */
    private const BADGE_TARGETS = [
        'first_practice' => 1,
        'master_alfabet' => 10,
        'master_angka' => 10,
        'sibi_explorer' => 5,
        'bisindo_explorer' => 5,
        'streak_7' => 7,
        'challenger' => 10,
        'perfect_round' => 1,
    ];

    /** Mapping badge (alfabet/angka) → slug kategori SignVerse. */
    private const CATEGORY_RULES = [
        'master_alfabet' => 'alphabet',
        'master_angka' => 'number',
    ];

    public static function levelFromXp(int $xp): array
    {
        $current = self::LEVELS[0][1];
        $idx = 0;
        foreach (self::LEVELS as $i => [$threshold, $name]) {
            if ($xp >= $threshold) {
                $current = $name;
                $idx = $i;
            }
        }

        $nextThreshold = self::LEVELS[$idx + 1][0] ?? null;
        $prevThreshold = self::LEVELS[$idx][0];
        $pct = 0.0;
        if ($nextThreshold !== null) {
            $pct = round(($xp - $prevThreshold) / ($nextThreshold - $prevThreshold) * 100, 1);
        }

        return [
            'name' => $current,
            'color' => self::LEVEL_COLORS[$current] ?? '#6366f1',
            'xp' => $xp,
            'threshold' => $prevThreshold,
            'next_xp' => $nextThreshold,
            'pct' => $pct,
        ];
    }

    public static function totalXp(int $userId): int
    {
        return Student::query()->where('user_id', $userId)->first()?->totalXp() ?? 0;
    }

    /** Hitung streak & last activity dari tanggal latihan student (distinct date). */
    public static function streakInfo(int $userId): array
    {
        $days = DB::table('practice_sessions as s')
            ->join('students as st', 'st.id', '=', 's.student_id')
            ->where('st.user_id', $userId)
            ->selectRaw('DATE(s.created_at) as d')
            ->distinct()
            ->orderByDesc('d')
            ->pluck('d')
            ->all();

        if ($days === []) {
            return ['streak' => 0, 'last_activity' => null, 'is_today' => false, 'days' => []];
        }

        $last = CarbonImmutable::parse($days[0])->startOfDay();
        $today = CarbonImmutable::today();

        $streak = 0;
        if ($last->toDateString() === $today->toDateString()
            || $last->toDateString() === $today->subDay()->toDateString()) {
            $set = array_flip($days);
            $d = $last;
            while (isset($set[$d->toDateString()])) {
                $streak++;
                $d = $d->subDay();
            }
        }

        return [
            'streak' => $streak,
            'last_activity' => $days[0],
            'is_today' => $last->toDateString() === $today->toDateString(),
            'days' => $days,
        ];
    }

    public static function badgeCatalog(): array
    {
        return DB::table('badges')->get()->keyBy('badge_key')->all();
    }

    /** Set badge_key yang sudah di-unlock user. */
    public static function unlockedKeys(int $userId): array
    {
        return DB::table('achievements as a')
            ->join('badges as b', 'b.id', '=', 'a.badge_id')
            ->where('a.user_id', $userId)
            ->pluck('b.badge_key')
            ->all();
    }

    private static function unlock(int $userId, string $badgeKey): bool
    {
        $badge = DB::table('badges')->where('badge_key', $badgeKey)->first();
        if ($badge === null) {
            return false;
        }

        $exists = DB::table('achievements')
            ->where('user_id', $userId)
            ->where('badge_id', $badge->id)
            ->exists();
        if ($exists) {
            return false;
        }

        DB::table('achievements')->insert(['user_id' => $userId, 'badge_id' => $badge->id]);

        return true;
    }

    /** Evaluasi unlock badge berdasarkan data latihan (dipanggil setelah latihan). */
    public static function evaluateBadges(int $userId): void
    {
        // (slug kategori) => {n, acc}
        $rows = DB::table('practice_sessions as s')
            ->join('lessons as l', 'l.id', '=', 's.lesson_id')
            ->join('materials as m', 'm.id', '=', 'l.material_id')
            ->join('categories as c', 'c.id', '=', 'm.category_id')
            ->where('s.student_id', self::studentId($userId))
            ->where('s.status', 'completed')
            ->groupBy('c.slug')
            ->selectRaw('c.slug as category, COUNT(*) as n, AVG(s.score) as acc')
            ->get();

        $catStats = [];
        foreach ($rows as $row) {
            $catStats[$row->category] = ['n' => (int) $row->n, 'acc' => (float) $row->acc];
        }

        $streak = self::streakInfo($userId)['streak'];
        $challengesDone = (int) DB::table('challenge_progress')
            ->where('user_id', $userId)
            ->where('completed', 1)
            ->count();

        $checks = [
            'first_practice' => DB::table('practice_sessions')
                ->where('student_id', self::studentId($userId))
                ->where('status', 'completed')->count() >= 1,
            'master_alfabet' => self::ruleMet($catStats, 'master_alfabet'),
            'master_angka' => self::ruleMet($catStats, 'master_angka'),
            'sibi_explorer' => self::countSign($userId, 'sibi') >= 5,
            'bisindo_explorer' => self::countSign($userId, 'bisindo') >= 5,
            'streak_7' => $streak >= 7,
            'challenger' => $challengesDone >= 10,
            'perfect_round' => DB::table('practice_sessions')
                ->where('student_id', self::studentId($userId))
                ->where('status', 'completed')
                ->where('score', '>=', 100)->count() >= 1,
        ];

        foreach ($checks as $badgeKey => $unlocked) {
            if ($unlocked) {
                self::unlock($userId, $badgeKey);
            }
        }
    }

    private static function ruleMet(array $catStats, string $badgeKey): bool
    {
        $slug = self::CATEGORY_RULES[$badgeKey];
        $stat = $catStats[$slug] ?? null;

        return $stat !== null && $stat['n'] >= 10 && $stat['acc'] >= 90;
    }

    private static function countSign(int $userId, string $language): int
    {
        return (int) DB::table('practice_sessions as s')
            ->join('lessons as l', 'l.id', '=', 's.lesson_id')
            ->join('materials as m', 'm.id', '=', 'l.material_id')
            ->where('s.student_id', self::studentId($userId))
            ->where('s.status', 'completed')
            ->where('m.language', $language)
            ->count();
    }

    /**
     * Progres menuju tiap badge {badge_key: {current, target}} — paritas
     * badge_progress(). Metrik mengikuti aturan evaluateBadges.
     */
    public static function badgeProgress(int $userId): array
    {
        $current = [
            'first_practice' => (int) DB::table('practice_sessions')
                ->where('student_id', self::studentId($userId))
                ->where('status', 'completed')->count(),
            'streak_7' => self::streakInfo($userId)['streak'],
            'challenger' => (int) DB::table('challenge_progress')
                ->where('user_id', $userId)->where('completed', 1)->count(),
            'perfect_round' => (int) DB::table('practice_sessions')
                ->where('student_id', self::studentId($userId))
                ->where('status', 'completed')
                ->where('score', '>=', 100)->count(),
        ];

        // Jumlah sesi per kategori & per sistem isyarat (satu query).
        $rows = DB::table('practice_sessions as s')
            ->join('lessons as l', 'l.id', '=', 's.lesson_id')
            ->join('materials as m', 'm.id', '=', 'l.material_id')
            ->join('categories as c', 'c.id', '=', 'm.category_id')
            ->where('s.student_id', self::studentId($userId))
            ->where('s.status', 'completed')
            ->groupBy('c.slug', 'm.language')
            ->selectRaw('c.slug as category, m.language as lang, COUNT(*) as n')
            ->get();

        $catN = [];
        $sysN = [];
        foreach ($rows as $row) {
            $catN[$row->category] = ($catN[$row->category] ?? 0) + (int) $row->n;
            $sysN[$row->lang] = ($sysN[$row->lang] ?? 0) + (int) $row->n;
        }
        $current['master_alfabet'] = $catN['alphabet'] ?? 0;
        $current['master_angka'] = $catN['number'] ?? 0;
        $current['sibi_explorer'] = $sysN['sibi'] ?? 0;
        $current['bisindo_explorer'] = $sysN['bisindo'] ?? 0;

        $progress = [];
        foreach (self::BADGE_TARGETS as $key => $target) {
            $n = (int) ($current[$key] ?? 0);
            $progress[$key] = ['current' => min($n, $target), 'target' => $target];
        }

        return $progress;
    }

    /** List semua badge (unlocked/locked) plus tanggal unlock. */
    public static function achievements(int $userId): array
    {
        $catalog = DB::table('badges')->orderBy('id')->get();
        $unlocks = DB::table('achievements as a')
            ->join('badges as b', 'b.id', '=', 'a.badge_id')
            ->where('a.user_id', $userId)
            ->pluck('a.unlocked_at', 'b.badge_key');

        $result = [];
        foreach ($catalog as $badge) {
            $result[] = [
                'key' => $badge->badge_key,
                'name' => $badge->name,
                'description' => $badge->description,
                'unlocked' => $unlocks->has($badge->badge_key),
                'unlocked_at' => $unlocks->get($badge->badge_key),
            ];
        }

        return $result;
    }

    /** Nama-nama badge dari kumpulan badge_keys (untuk flash message). */
    public static function badgeNames(array $badgeKeys): array
    {
        return collect($badgeKeys)
            ->map(fn (string $key) => DB::table('badges')->where('badge_key', $key)->value('name'))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Leaderboard XP antar student (untuk widget achievement).
     * Dipertahankan dari desain SignVerse; XP memakai agregat versi baru.
     */
    public function leaderboard(Student $student, int $limit = 5): Collection
    {
        return Student::query()
            ->with('user:id,name')
            ->get()
            ->map(fn (Student $row) => [
                'student' => $row,
                'xp' => $row->totalXp(),
            ])
            ->sortByDesc('xp')
            ->take($limit)
            ->values();
    }

    private static function studentId(int $userId): ?int
    {
        return Student::query()->where('user_id', $userId)->value('id');
    }
}
