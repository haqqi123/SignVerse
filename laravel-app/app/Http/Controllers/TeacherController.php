<?php

namespace App\Http\Controllers;

use App\Services\AssignmentService;
use App\Services\LearningService;
use App\Services\TeacherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherController extends Controller
{
    /** Dasbor utama guru: ringkasan metrik kelas, grafik aktivitas mingguan, tugas terbaru. */
    public function dashboard(): View
    {
        $uid = auth()->id();

        $summary = TeacherService::teacherSummary();
        $weekly = TeacherService::weeklyActivity(7);
        $assignments = AssignmentService::teacherAssignments($uid);

        return view('teacher.dashboard', [
            'summary' => $summary,
            'weekly' => $weekly,
            'assignments' => $assignments,
        ]);
    }

    /** Daftar seluruh siswa bimbingan beserta riwayat dan statistik singkat. */
    public function students(): View
    {
        $students = TeacherService::allStudents();
        $report = TeacherService::classReport();

        return view('teacher.students', [
            'students' => $students,
            'report' => $report,
        ]);
    }

    /** Monitoring mendalam per siswa (grafik performa individu, evaluasi gestur, riwayat sesi). */
    public function monitoring(Request $request): View
    {
        $students = TeacherService::allStudents();
        $selectedId = (int) $request->query('student_id');

        if (! $selectedId && $students->isNotEmpty()) {
            $selectedId = $students->first()->id;
        }

        $detail = $selectedId ? TeacherService::studentDetail($selectedId) : null;

        return view('teacher.monitoring', [
            'students' => $students,
            'selectedId' => $selectedId,
            'detail' => $detail,
        ]);
    }

    /** Manajemen tugas kelas (daftar tugas & formulir pembuatan tugas baru). */
    public function assignments(): View
    {
        $uid = auth()->id();

        $assignments = AssignmentService::teacherAssignments($uid);
        $materials = LearningService::getMaterials();
        $students = TeacherService::allStudents();

        return view('teacher.assignments', [
            'assignments' => $assignments,
            'materials' => $materials,
            'students' => $students,
        ]);
    }

    /** Buat tugas baru oleh guru. */
    public function createAssignment(Request $request): RedirectResponse
    {
        $teacherId = auth()->id();

        $validated = $request->validate([
            'material_id' => ['required', 'integer', 'exists:materials,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:users,id'],
            'deadline' => ['required', 'date'],
        ]);

        AssignmentService::createAssignment(
            $teacherId,
            (int) $validated['material_id'],
            $validated['title'],
            $validated['description'] ?? '',
            $validated['student_ids'],
            $validated['deadline'],
        );

        return back()->with('success', 'Assignment berhasil dibuat untuk ' . count($validated['student_ids']) . ' siswa!');
    }

    /** Laporan kelas & individu. */
    public function reports(Request $request): View
    {
        $report = TeacherService::classReport();
        $students = TeacherService::allStudents();
        $selectedId = (int) $request->query('student_id');

        $detail = $selectedId ? TeacherService::studentDetail($selectedId) : null;

        return view('teacher.reports', [
            'report' => $report,
            'students' => $students,
            'selectedId' => $selectedId,
            'detail' => $detail,
        ]);
    }

    /** Ekspor laporan kelas ke format CSV (UTF-8 with BOM). */
    public function exportReport(): StreamedResponse
    {
        $report = TeacherService::classReport();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="class_report_signteach.csv"',
        ];

        return response()->stream(function () use ($report) {
            $handle = fopen('php://output', 'w');
            // Tambahkan BOM untuk UTF-8 compatibility Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Nama Siswa', 'Akurasi Rata-rata (%)', 'Skor Rata-rata', 'Jumlah Sesi', 'Materi Dipelajari', 'Terakhir Aktif']);

            foreach ($report as $r) {
                fputcsv($handle, [
                    $r['name'],
                    $r['accuracy'],
                    $r['score'],
                    $r['sessions'],
                    $r['materials'],
                    $r['last_activity'] ?? '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /** Halaman profil guru. */
    public function profile(): View
    {
        return view('teacher.profile', [
            'user' => auth()->user(),
        ]);
    }

    /** Pembaruan nama & kata sandi profil guru. */
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

        return back()->with('success', 'Profil guru berhasil diperbarui.');
    }
}
