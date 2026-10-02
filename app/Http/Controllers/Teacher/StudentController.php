<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\TeacherStatsService;
use Illuminate\View\View;

/**
 * Monitoring siswa (Phase 7): daftar seluruh siswa dengan ringkasan
 * aktivitas + halaman detail per siswa.
 */
class StudentController extends Controller
{
    public function __construct(
        private TeacherStatsService $stats,
    ) {}

    /**
     * Daftar siswa untuk monitoring.
     */
    public function index(): View
    {
        return view('teacher.students.index', [
            'students' => $this->stats->studentsOverview(),
        ]);
    }

    /**
     * Detail satu siswa: statistik, progress kategori, sesi terakhir,
     * achievement, dan gesture yang sering salah oleh siswa tersebut.
     */
    public function show(Student $student): View
    {
        return view('teacher.students.show', [
            'detail' => $this->stats->studentDetail($student),
            'errorAnalysis' => $this->stats->errorAnalysisForStudent($student),
        ]);
    }
}
