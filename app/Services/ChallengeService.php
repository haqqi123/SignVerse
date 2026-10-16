<?php

namespace App\Services;

use App\Models\Challenge;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/*
 * Service untuk tantangan harian (Daily Challenges).
 *
 * Kategori challenge memakai enum ('alfabet','angka','kosakata');
 * pemetaan dari slug kategori SignVerse ada di slugToCategory().
 */
class ChallengeService
{
    /** Pemetaan slug kategori SignVerse → enum kategori challenge. */
    public const CATEGORY_MAP = [
        'alphabet' => 'alfabet',
        'number' => 'angka',
        'word' => 'kosakata',
        'phrase' => 'kosakata',
    ];

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
     * Segarkan challenge hari ini (idempoten): dipanggil sebelum halaman challenge ditampilkan agar
     * sistem tetap jalan tanpa seeding harian manual.
     */
    public static function ensureTodayChallenges(?string $date = null): Collection
    {
        $today = now()->toDateString();
        $rows = [
            [$today, 'Praktik 5 huruf', 'Lakukan 5 latihan huruf alfabet hari ini.', 'alfabet', 5],
            [$today, 'Praktik 3 kosakata', 'Lakukan 3 latihan kata kosakata hari ini.', 'kosakata', 3],
        ];

        foreach ($rows as [$date, $title, $description, $category, $target]) {
            Challenge::updateOrCreate(
                ['challenge_date' => $date, 'category' => $category],
                ['title' => $title, 'description' => $description, 'target' => $target, 'reward_xp' => 20],
            );
        }

        return self::todayChallenges($date);
    }

    /**
     * Ambil status tantangan harian untuk siswa (beserta progres & status selesai).
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
     * Menambah progres tantangan harian sesuai kategori latihan yang diselesaikan.
     * Mengembalikan true jika ada tantangan yang baru saja selesai (unlocked).
     */
    public static function bumpChallenge(int $userId, string $categorySlug, bool $completedAll = false): bool
    {
        $category = self::CATEGORY_MAP[$categorySlug] ?? $categorySlug;
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
