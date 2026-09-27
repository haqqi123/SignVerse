<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/*
 * Service untuk tantangan harian (Daily Challenges).
 * Paritas penuh dengan fungsi challenge di signlib/service.py.
 */
class ChallengeService
{
    /** Ambil daftar tantangan pada tanggal tertentu (default hari ini). */
    public static function todayChallenges(?string $date = null): Collection
    {
        $d = $date ?? CarbonImmutable::today()->toDateString();

        return DB::table('challenges')
            ->where('challenge_date', $d)
            ->orderBy('id')
            ->get();
    }

    /**
     * Ambil status tantangan harian untuk siswa (beserta progres & status selesai).
     * Paritas challenge_status() di signlib/service.py.
     */
    public static function challengeStatus(int $userId, ?string $date = null): Collection
    {
        $d = $date ?? CarbonImmutable::today()->toDateString();

        return DB::table('challenges as c')
            ->leftJoin('challenge_progress as cp', function ($join) use ($userId) {
                $join->on('cp.challenge_id', '=', 'c.id')
                    ->where('cp.user_id', '=', $userId);
            })
            ->where('c.challenge_date', $d)
            ->orderBy('c.id')
            ->select([
                'c.*',
                DB::raw('COALESCE(cp.progress, 0) as progress'),
                DB::raw('COALESCE(cp.completed, 0) as completed'),
                'cp.completed_at',
            ])
            ->get();
    }

    /**
     * Menambah progres tantangan harian berdasarkan kategori latihan yang diselesaikan.
     * Paritas bump_challenge() di signlib/service.py.
     * Mengembalikan true jika ada tantangan yang baru saja selesai (unlocked).
     */
    public static function bumpChallenge(int $userId, string $category, bool $completedAll = false): bool
    {
        $today = CarbonImmutable::today()->toDateString();

        $challenges = DB::table('challenges as c')
            ->leftJoin('challenge_progress as cp', function ($join) use ($userId) {
                $join->on('cp.challenge_id', '=', 'c.id')
                    ->where('cp.user_id', '=', $userId);
            })
            ->where('c.challenge_date', $today)
            ->where('c.category', $category)
            ->select([
                'c.id',
                'c.target',
                'c.reward_xp',
                DB::raw('COALESCE(cp.progress, 0) as current_progress'),
                DB::raw('COALESCE(cp.completed, 0) as is_completed'),
            ])
            ->get();

        $unlockedAny = false;
        foreach ($challenges as $ch) {
            $newProgress = $completedAll ? $ch->target : $ch->current_progress + 1;
            $completed = $newProgress >= $ch->target ? 1 : 0;

            $exists = DB::table('challenge_progress')
                ->where('user_id', $userId)
                ->where('challenge_id', $ch->id)
                ->exists();

            if (! $exists) {
                DB::table('challenge_progress')->insert([
                    'user_id' => $userId,
                    'challenge_id' => $ch->id,
                    'progress' => $newProgress,
                    'completed' => $completed,
                    'completed_at' => $completed ? CarbonImmutable::now() : null,
                ]);
            } else {
                DB::table('challenge_progress')
                    ->where('user_id', $userId)
                    ->where('challenge_id', $ch->id)
                    ->update([
                        'progress' => $newProgress,
                        'completed' => $completed,
                        'completed_at' => $completed && ! $ch->is_completed ? CarbonImmutable::now() : DB::raw('completed_at'),
                    ]);
            }

            if ($completed && ! $ch->is_completed) {
                $unlockedAny = true;
            }
        }

        return $unlockedAny;
    }

    /**
     * Riwayat reward tantangan yang telah diselesaikan user.
     * Paritas challenge_rewards() di signlib/service.py.
     */
    public static function challengeRewards(int $userId): Collection
    {
        return DB::table('challenge_progress as cp')
            ->join('challenges as c', 'c.id', '=', 'cp.challenge_id')
            ->where('cp.user_id', $userId)
            ->where('cp.completed', 1)
            ->orderByDesc('cp.completed_at')
            ->select([
                'c.title',
                'c.category',
                'c.reward_xp',
                'cp.completed_at',
            ])
            ->get();
    }
}
