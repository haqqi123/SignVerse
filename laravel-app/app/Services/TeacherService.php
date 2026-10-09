<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/*
 * Service untuk analitik kelas dan pemantauan siswa oleh guru.
 * Paritas penuh dengan fungsi guru di signlib/service.py.
 */
class TeacherService
{
    /** Ambil semua siswa beserta jumlah total sesi latihannya. */
    public static function allStudents(): Collection
    {
        return DB::table('users as u')
            ->leftJoin('practice_sessions as s', 's.user_id', '=', 'u.id')
            ->where('u.role', 'student')
            ->groupBy('u.id', 'u.name', 'u.username')
            ->orderBy('u.name')
            ->select([
                'u.id',
                'u.name',
                'u.username',
                DB::raw('COUNT(s.id) as n_sessions'),
            ])
            ->get();
    }

    /**
     * Ringkasan performa kelas untuk dashboard guru.
     * Paritas teacher_summary() di signlib/service.py.
     */
    public static function teacherSummary(): array
    {
        $totalStudents = (int) DB::table('users')->where('role', 'student')->count();
        $avgScore = DB::table('practice_sessions')->avg('final_score') ?? 0.0;

        $sinceWeekly = CarbonImmutable::now()->subDays(7)->startOfDay();
        $weeklySessions = (int) DB::table('practice_sessions')
            ->where('created_at', '>=', $sinceWeekly)
            ->count();

        $today = CarbonImmutable::today()->toDateString();
        $activeAssignments = (int) DB::table('assignments')
            ->where('deadline', '>=', $today)
            ->count();

        return [
            'students' => $totalStudents,
            'avg_score' => round((float) $avgScore, 1),
            'weekly_sessions' => $weeklySessions,
            'active_assignments' => $activeAssignments,
        ];
    }

    /**
     * Detail komprehensif satu siswa (untuk halaman monitoring guru).
     * Paritas student_detail() di signlib/service.py.
     */
    public static function studentDetail(int $userId): ?array
    {
        $user = DB::table('users')
            ->where('id', $userId)
            ->where('role', 'student')
            ->first();

        if (! $user) {
            return null;
        }

        $stats = StudentService::studentStats($userId);
        $categoryAcc = StudentService::categoryAccuracy($userId);
        $errors = StudentService::gestureErrors($userId, 5);
        $lastActivity = DB::table('practice_sessions')
            ->where('user_id', $userId)
            ->max('created_at');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'created_at' => $user->created_at ?? null,
            'stats' => $stats,
            'category_accuracy' => $categoryAcc,
            'errors' => $errors,
            'last_activity' => $lastActivity ? substr($lastActivity, 0, 10) : null,
            'history' => StudentService::practiceHistory($userId, 10),
            'achievements' => GamificationService::achievements($userId),
        ];
    }

    /**
     * Laporan lengkap kelas per siswa.
     * Paritas class_report() di signlib/service.py.
     */
    public static function classReport(): array
    {
        $students = self::allStudents();
        $report = [];

        foreach ($students as $s) {
            $row = DB::table('practice_sessions')
                ->where('user_id', $s->id)
                ->selectRaw('AVG(accuracy) as acc, AVG(final_score) as score, COUNT(*) as n, COUNT(DISTINCT material_id) as materials')
                ->first();

            $last = DB::table('practice_sessions')
                ->where('user_id', $s->id)
                ->max('created_at');

            $report[] = [
                'id' => $s->id,
                'name' => $s->name,
                'accuracy' => round((float) ($row->acc ?? 0), 1),
                'score' => round((float) ($row->score ?? 0), 1),
                'sessions' => (int) ($row->n ?? 0),
                'materials' => (int) ($row->materials ?? 0),
                'last_activity' => $last ? substr($last, 0, 10) : null,
            ];
        }

        return $report;
    }

    /**
     * Tren aktivitas latihan mingguan kelas (jumlah sesi per hari).
     * Paritas weekly_activity() di signlib/service.py.
     */
    public static function weeklyActivity(int $days = 7): array
    {
        $since = CarbonImmutable::now()->subDays($days)->startOfDay();

        $rows = DB::table('practice_sessions')
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as n')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('d')
            ->get();

        return $rows->map(fn ($r) => [
            'day' => $r->d,
            'count' => (int) $r->n,
        ])->all();
    }
}
