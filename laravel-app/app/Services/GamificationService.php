<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/*
 * Gamifikasi SignTeach: XP, level, streak, dan badge — paritas penuh
 * dengan signlib/gamification.py (query & aturan dipindah apa adanya).
 */
class GamificationService
{
    /** [threshold_xp, nama_level] — paritas LEVELS di gamification.py. */
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
        $sessions = (int) DB::table('practice_sessions')->where('user_id', $userId)->sum('xp_earned');
        $bonus = (int) DB::table('challenge_progress as cp')
            ->join('challenges as c', 'c.id', '=', 'cp.challenge_id')
            ->where('cp.user_id', $userId)
            ->where('cp.completed', 1)
            ->sum('c.reward_xp');

        return $sessions + $bonus;
    }

    /** Hitung streak & last activity dari tanggal latihan user (distinct date). */
    public static function streakInfo(int $userId): array
    {
        $days = DB::table('practice_sessions')
            ->where('user_id', $userId)
            ->selectRaw('DATE(created_at) as d')
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
        // (category, sign_system) => {n, acc} — paritas cat_stats.
        // Alias "sys" karena SYSTEM reserved word di MySQL 8.
        $rows = DB::table('practice_sessions as s')
            ->join('materials as m', 'm.id', '=', 's.material_id')
            ->where('s.user_id', $userId)
            ->groupBy('m.category', 'm.sign_system')
            ->selectRaw('m.category as category, m.sign_system as sys, COUNT(*) as n, AVG(s.accuracy) as acc')
            ->get();

        $catStats = [];
        foreach ($rows as $row) {
            $catStats[$row->category.'|'.$row->sys] = ['n' => (int) $row->n, 'acc' => (float) $row->acc];
        }

        // Agregasi satu kategori lintas sistem isyarat (SIBI + BISINDO).
        $statCat = function (string $cat) use ($catStats): array {
            $n = 0;
            $accWeighted = 0.0;
            foreach ($catStats as $key => $stat) {
                if (str_starts_with($key, $cat.'|')) {
                    $n += $stat['n'];
                    $accWeighted += $stat['acc'] * $stat['n'];
                }
            }

            return ['n' => $n, 'acc' => $n > 0 ? $accWeighted / $n : 0.0];
        };

        $alfabet = $statCat('alfabet');
        $angka = $statCat('angka');
        $streak = self::streakInfo($userId)['streak'];
        $challengesDone = (int) DB::table('challenge_progress')
            ->where('user_id', $userId)
            ->where('completed', 1)
            ->count();

        $checks = [
            'first_practice' => DB::table('practice_sessions')->where('user_id', $userId)->count() >= 1,
            'master_alfabet' => $alfabet['n'] >= 10 && $alfabet['acc'] >= 90,
            'master_angka' => $angka['n'] >= 10 && $angka['acc'] >= 90,
            'sibi_explorer' => self::countSign($userId, 'SIBI') >= 5,
            'bisindo_explorer' => self::countSign($userId, 'BISINDO') >= 5,
            'streak_7' => $streak >= 7,
            'challenger' => $challengesDone >= 10,
            'perfect_round' => DB::table('practice_sessions')
                ->where('user_id', $userId)->where('accuracy', '>=', 100)->count() >= 1,
        ];

        foreach ($checks as $badgeKey => $unlocked) {
            if ($unlocked) {
                self::unlock($userId, $badgeKey);
            }
        }
    }

    private static function countSign(int $userId, string $sign): int
    {
        return (int) DB::table('practice_sessions as s')
            ->join('materials as m', 'm.id', '=', 's.material_id')
            ->where('s.user_id', $userId)
            ->where('m.sign_system', $sign)
            ->count();
    }

    /**
     * Progres menuju tiap badge {badge_key: {current, target}} — paritas
     * badge_progress(). Metrik mengikuti aturan evaluateBadges.
     */
    public static function badgeProgress(int $userId): array
    {
        $current = [
            'first_practice' => (int) DB::table('practice_sessions')->where('user_id', $userId)->count(),
            'streak_7' => self::streakInfo($userId)['streak'],
            'challenger' => (int) DB::table('challenge_progress')
                ->where('user_id', $userId)->where('completed', 1)->count(),
            'perfect_round' => (int) DB::table('practice_sessions')
                ->where('user_id', $userId)->where('accuracy', '>=', 100)->count(),
        ];

        // Jumlah sesi per kategori & per sistem isyarat (satu query).
        $rows = DB::table('practice_sessions as s')
            ->join('materials as m', 'm.id', '=', 's.material_id')
            ->where('s.user_id', $userId)
            ->groupBy('m.category', 'm.sign_system')
            ->selectRaw('m.category as category, m.sign_system as sys, COUNT(*) as n')
            ->get();

        $catN = [];
        $sysN = [];
        foreach ($rows as $row) {
            $catN[$row->category] = ($catN[$row->category] ?? 0) + (int) $row->n;
            $sysN[$row->sys] = ($sysN[$row->sys] ?? 0) + (int) $row->n;
        }
        $current['master_alfabet'] = $catN['alfabet'] ?? 0;
        $current['master_angka'] = $catN['angka'] ?? 0;
        $current['sibi_explorer'] = $sysN['SIBI'] ?? 0;
        $current['bisindo_explorer'] = $sysN['BISINDO'] ?? 0;

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
}
