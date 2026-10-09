<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/*
 * Service untuk analitik & statistik siswa.
 * Paritas penuh dengan fungsi statistik siswa di signlib/service.py & signlib/recommendation.py.
 */
class StudentService
{
    /**
     * Statistik inti untuk dashboard siswa.
     * Paritas student_stats() di signlib/service.py.
     */
    public static function studentStats(int $userId): array
    {
        $totalSessions = (int) DB::table('practice_sessions')->where('user_id', $userId)->count();
        $avgAcc = DB::table('practice_sessions')->where('user_id', $userId)->avg('accuracy') ?? 0.0;
        $materialsDone = (int) DB::table('practice_sessions')
            ->where('user_id', $userId)
            ->whereNotNull('material_id')
            ->distinct('material_id')
            ->count('material_id');
        $challengesDone = (int) DB::table('challenge_progress')
            ->where('user_id', $userId)
            ->where('completed', 1)
            ->count();

        return [
            'total_sessions' => $totalSessions,
            'avg_accuracy' => round((float) $avgAcc, 1),
            'materials_done' => $materialsDone,
            'challenges_done' => $challengesDone,
        ];
    }

    /**
     * Akurasi rata-rata per kategori materi (alfabet, angka, kosakata).
     * Paritas category_accuracy() di signlib/service.py.
     */
    public static function categoryAccuracy(int $userId): array
    {
        $rows = DB::table('practice_sessions as s')
            ->join('materials as m', 'm.id', '=', 's.material_id')
            ->where('s.user_id', $userId)
            ->groupBy('m.category')
            ->selectRaw('m.category, AVG(s.accuracy) as acc, COUNT(*) as n')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->category] = [
                'accuracy' => round((float) $row->acc, 1),
                'count' => (int) $row->n,
            ];
        }

        return $result;
    }

    /**
     * Rata-rata akurasi harian (untuk visualisasi grafik tren akurasi).
     * Paritas accuracy_trend() di signlib/service.py.
     */
    public static function accuracyTrend(int $userId, int $days = 7): array
    {
        $since = CarbonImmutable::now()->subDays($days)->startOfDay();

        $rows = DB::table('practice_sessions')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as d, AVG(accuracy) as acc, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('d')
            ->get();

        return $rows->map(fn ($r) => [
            'day' => $r->d,
            'accuracy' => round((float) $r->acc, 1),
            'count' => (int) $r->count,
        ])->all();
    }

    /**
     * Histori sesi latihan terbaru.
     * Paritas practice_history() di signlib/service.py.
     */
    public static function practiceHistory(int $userId, int $limit = 30): Collection
    {
        return DB::table('practice_sessions as s')
            ->leftJoin('materials as m', 'm.id', '=', 's.material_id')
            ->leftJoin('lessons as l', 'l.id', '=', 's.lesson_id')
            ->where('s.user_id', $userId)
            ->orderByDesc('s.created_at')
            ->limit($limit)
            ->select([
                's.*',
                'm.title as material_title',
                'm.category as material_category',
                'm.sign_system as sign_system',
                'l.title as lesson_title',
            ])
            ->get();
    }

    /**
     * Analisis kesalahan gestur berdasar hasil deteksi nyata.
     * Paritas gesture_errors() di signlib/service.py.
     */
    public static function gestureErrors(int $userId, int $limit = 5): array
    {
        $rows = DB::table('gesture_results as gr')
            ->join('practice_sessions as s', 's.id', '=', 'gr.session_id')
            ->where('s.user_id', $userId)
            ->groupBy('gr.expected')
            ->selectRaw('gr.expected, COUNT(*) as n, AVG(gr.is_correct) * 100 as acc, SUM(gr.is_correct) as correct, COUNT(*) - SUM(gr.is_correct) as wrong')
            ->havingRaw('COUNT(*) > 0')
            ->orderBy('acc')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'expected' => $r->expected,
            'n' => (int) $r->n,
            'accuracy' => round((float) $r->acc, 1),
            'correct' => (int) $r->correct,
            'wrong' => (int) $r->wrong,
        ])->all();
    }

    /**
     * Rekomendasi belajar adaptif berbasis data latihan (rule-based).
     * Paritas penuh dengan next_recommendation() di signlib/recommendation.py.
     */
    public static function recommendation(int $userId): array
    {
        $cat = self::categoryAccuracy($userId);
        $labels = [
            'alfabet' => 'Alfabet',
            'angka' => 'Angka',
            'kosakata' => 'Kosakata Dasar',
        ];

        if ($cat === []) {
            return [
                'title' => 'Mulai dari Alfabet',
                'text' => 'Belum ada data latihan. Mulailah dengan Alfabet SIBI untuk membangun dasar isyarat.',
                'category' => 'alfabet',
            ];
        }

        // Kategori dengan akurasi terendah yang pernah dicoba
        $weakestCat = null;
        $lowestAcc = 101.0;
        foreach ($cat as $name => $data) {
            if ($data['accuracy'] < $lowestAcc) {
                $lowestAcc = $data['accuracy'];
                $weakestCat = $name;
            }
        }

        $label = $labels[$weakestCat] ?? ucfirst($weakestCat);

        if ($lowestAcc < 85) {
            $text = "Latih kembali {$label} karena akurasi rata-rata kamu pada kategori ini masih {$lowestAcc}%.";
        } elseif ($lowestAcc >= 95) {
            $text = "Kinerja {$label} sudah sangat baik. Lanjutkan ke materi berikutnya untuk variasi isyarat.";
        } else {
            $text = "Pertahankan latihan {$label} untuk menguasai kategori ini.";
        }

        return [
            'title' => 'Direkomendasikan untukmu',
            'text' => $text,
            'category' => $weakestCat,
            'accuracy' => $lowestAcc,
        ];
    }
}
