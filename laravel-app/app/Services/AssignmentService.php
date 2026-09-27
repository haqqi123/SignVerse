<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/*
 * Service untuk tugas/assignment siswa dan guru.
 * Paritas penuh dengan fungsi assignment di signlib/service.py.
 */
class AssignmentService
{
    /**
     * Buat penugasan baru oleh guru ke satu atau beberapa siswa.
     * Paritas create_assignment() di signlib/service.py.
     */
    public static function createAssignment(
        int $teacherId,
        int $materialId,
        string $title,
        string $description,
        array $studentIds,
        string $deadline,
    ): int {
        $assignmentId = DB::table('assignments')->insertGetId([
            'teacher_id' => $teacherId,
            'material_id' => $materialId,
            'title' => $title,
            'description' => $description,
            'deadline' => $deadline,
            'created_at' => CarbonImmutable::now(),
        ]);

        foreach ($studentIds as $studentId) {
            DB::table('assignment_items')->insertOrIgnore([
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
                'status' => 'belum dimulai',
            ]);
        }

        return $assignmentId;
    }

    /**
     * Daftar assignment yang dibuat oleh guru tertentu beserta progres penyelesaian kelas.
     * Paritas teacher_assignments() di signlib/service.py.
     */
    public static function teacherAssignments(int $teacherId): Collection
    {
        return DB::table('assignments as a')
            ->leftJoin('materials as m', 'm.id', '=', 'a.material_id')
            ->where('a.teacher_id', $teacherId)
            ->orderByDesc('a.created_at')
            ->select([
                'a.*',
                'm.title as material_title',
                DB::raw('(SELECT COUNT(*) FROM assignment_items ai WHERE ai.assignment_id = a.id) as total_students'),
                DB::raw("(SELECT COUNT(*) FROM assignment_items ai WHERE ai.assignment_id = a.id AND ai.status = 'selesai') as done"),
            ])
            ->get();
    }

    /**
     * Detail satu assignment.
     * Paritas assignment_detail() di signlib/service.py.
     */
    public static function assignmentDetail(int $assignmentId): ?object
    {
        return DB::table('assignments as a')
            ->leftJoin('materials as m', 'm.id', '=', 'a.material_id')
            ->join('users as u', 'u.id', '=', 'a.teacher_id')
            ->where('a.id', $assignmentId)
            ->select([
                'a.*',
                'm.title as material_title',
                'u.name as teacher_name',
            ])
            ->first();
    }

    /**
     * Daftar siswa penerima tugas beserta status masing-masing.
     * Paritas assignment_students() di signlib/service.py.
     */
    public static function assignmentStudents(int $assignmentId): Collection
    {
        return DB::table('assignment_items as ai')
            ->join('users as u', 'u.id', '=', 'ai.student_id')
            ->where('ai.assignment_id', $assignmentId)
            ->orderBy('u.name')
            ->select([
                'ai.*',
                'u.name as student_name',
                'u.username as student_username',
            ])
            ->get();
    }

    /**
     * Daftar assignment yang ditujukan ke satu siswa beserta statusnya.
     * Paritas student_assignments() di signlib/service.py.
     */
    public static function studentAssignments(int $userId): Collection
    {
        return DB::table('assignment_items as ai')
            ->join('assignments as a', 'a.id', '=', 'ai.assignment_id')
            ->leftJoin('materials as m', 'm.id', '=', 'a.material_id')
            ->where('ai.student_id', $userId)
            ->orderByDesc('a.created_at')
            ->select([
                'a.id as assignment_id',
                'a.title',
                'a.description',
                'a.deadline',
                'a.material_id',
                'm.title as material_title',
                'm.category as material_category',
                'ai.status',
                'ai.completed_at',
            ])
            ->get();
    }

    /**
     * Update status penugasan siswa (belum dimulai / sedang dikerjakan / selesai).
     * Paritas update_assignment_status() di signlib/service.py.
     */
    public static function updateAssignmentStatus(int $userId, int $assignmentId, string $status): void
    {
        $isCompleted = ($status === 'selesai');

        DB::table('assignment_items')
            ->where('student_id', $userId)
            ->where('assignment_id', $assignmentId)
            ->update([
                'status' => $status,
                'completed_at' => $isCompleted ? CarbonImmutable::now() : null,
            ]);
    }

    /**
     * Selesaikan assignment berstatus 'sedang dikerjakan' untuk materi tertentu secara otomatis.
     * Dipanggil setelah siswa menuntaskan latihan materi yang bersangkutan.
     * Paritas complete_assignments_for_material() di signlib/service.py.
     *
     * @return array Daftar judul assignment yang baru saja selesai.
     */
    public static function completeAssignmentsForMaterial(int $userId, int $materialId): array
    {
        if ($materialId <= 0) {
            return [];
        }

        $active = DB::table('assignment_items as ai')
            ->join('assignments as a', 'a.id', '=', 'ai.assignment_id')
            ->where('ai.student_id', $userId)
            ->where('a.material_id', $materialId)
            ->where('ai.status', 'sedang dikerjakan')
            ->select(['a.id as assignment_id', 'a.title'])
            ->get();

        $titles = [];
        $now = CarbonImmutable::now();
        foreach ($active as $item) {
            DB::table('assignment_items')
                ->where('student_id', $userId)
                ->where('assignment_id', $item->assignment_id)
                ->update([
                    'status' => 'selesai',
                    'completed_at' => $now,
                ]);

            $titles[] = $item->title;
        }

        return $titles;
    }
}
