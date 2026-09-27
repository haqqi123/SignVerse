<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Material;
use App\Models\PracticeSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/*
 * Service untuk domain Pembelajaran (Materi, Pelajaran, Sesi Terakhir).
 * Paritas penuh dengan fungsi materi & lesson di signlib/service.py.
 */
class LearningService
{
    /** Ambil semua materi atau difilter berdasarkan kategori. */
    public static function getMaterials(?string $category = null): Collection
    {
        $query = Material::withCount('lessons')->orderBy('sort_order');
        if ($category !== null) {
            $query->where('category', $category)->orderBy('sign_system');
        }

        return $query->get();
    }

    /** Ambil materi berdasarkan sistem isyarat (SIBI / BISINDO). */
    public static function getMaterialsBySystem(string $signSystem): Collection
    {
        return Material::withCount('lessons')
            ->where('sign_system', $signSystem)
            ->orderBy('sort_order')
            ->get();
    }

    /** Ambil daftar lesson milik materi tertentu. */
    public static function getLessons(int $materialId): Collection
    {
        return Lesson::where('material_id', $materialId)
            ->orderBy('sort_order')
            ->get();
    }

    /** Ambil satu lesson berdasarkan ID. */
    public static function getLesson(int $lessonId): ?Lesson
    {
        return Lesson::with('material')->find($lessonId);
    }

    /** Ambil satu material berdasarkan ID. */
    public static function getMaterial(int $materialId): ?Material
    {
        return Material::with('lessons')->find($materialId);
    }

    /** Lesson pertama dari sebuah materi (untuk tombol Mulai Belajar langsung). */
    public static function firstLesson(int $materialId): ?Lesson
    {
        return Lesson::where('material_id', $materialId)
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Sesi latihan terakhir user (untuk kartu "Lanjutkan Belajar" / Continue Learning).
     * Paritas dengan last_lesson() di signlib/service.py.
     */
    public static function lastLesson(int $userId): ?object
    {
        return DB::table('practice_sessions as s')
            ->leftJoin('lessons as l', 'l.id', '=', 's.lesson_id')
            ->leftJoin('materials as m', 'm.id', '=', 's.material_id')
            ->where('s.user_id', $userId)
            ->orderByDesc('s.created_at')
            ->select([
                's.*',
                'l.title as lesson_title',
                'l.target as lesson_target',
                'm.title as material_title',
                'm.category as category',
                'm.sign_system as sign_system',
            ])
            ->first();
    }

    /**
     * Progres belajar per materi untuk siswa tertentu.
     * Mengembalikan array [material_id => ['sessions_count' => n, 'avg_accuracy' => x, 'completed' => bool]].
     */
    public static function materialProgress(int $userId): array
    {
        $rows = DB::table('practice_sessions')
            ->where('user_id', $userId)
            ->whereNotNull('material_id')
            ->groupBy('material_id')
            ->selectRaw('material_id, COUNT(*) as sessions_count, AVG(accuracy) as avg_accuracy')
            ->get();

        $progress = [];
        foreach ($rows as $row) {
            $progress[$row->material_id] = [
                'sessions_count' => (int) $row->sessions_count,
                'avg_accuracy' => round((float) $row->avg_accuracy, 1),
                'completed' => (int) $row->sessions_count >= 1 && (float) $row->avg_accuracy >= 80,
            ];
        }

        return $progress;
    }
}
