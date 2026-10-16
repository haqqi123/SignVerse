# SignVerse — Laravel

Platform pembelajaran Bahasa Isyarat Indonesia (SIBI & BISINDO) dengan AI Practice,
gamification (XP, level, streak), challenge harian, achievement, assignment, dan
monitoring/laporan guru. Rebuild full-stack dari project lama Python/Streamlit.

## Stack

- Laravel 12 (PHP 8.2+), Blade + Tailwind CSS 3, Vite, Alpine.js (bawaan Breeze)
- MySQL 8
- Python AI service (FastAPI + YOLOv8 `best.pt`) — menyusul di Phase 9

## Setup Development

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
# pastikan MySQL berjalan & database signverse dibuat:
#   CREATE DATABASE signverse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
php artisan migrate --seed
php artisan serve
```

## Akun Demo (development)

| Role | Email | Password |
|---|---|---|
| Teacher | guru@signteach.id | guru123 |
| Student | siswa@signteach.id | siswa123 |
| Student | ahmad@signteach.id | siswa123 |
| Student | dewi@signteach.id | siswa123 |
| Student | bima@signteach.id | siswa123 |
| Student | siti@signteach.id | siswa123 |

Registrasi publik hanya membuat akun **student**; akun teacher dikelola via seeder.

## Struktur

- `app/Http/Controllers/Student` — modul student
- `app/Http/Controllers/Teacher` — modul teacher
- `app/Http/Middleware/EnsureRole.php` — guard role `student` / `teacher`
- `app/Models` — 11 model Eloquent: User/Student/Teacher + konten (Category,
  Material, Lesson), practice (PracticeSession, GestureResult), gamifikasi
  (Achievement, Challenge, ChallengeAttempt), assignment (Assignment,
  AssignmentSubmission)
- `database/seeders` — akun demo (1 guru + 5 siswa), konten pembelajaran
  (4 kategori, 5 materi, 20 lesson), 5 achievement, challenge harian
  7 hari terakhir, + data latihan demo untuk monitoring guru
- `app/Services` —
  `StudentStatsService`: statistik dashboard/progress student (XP & level
  agregat, streak, lesson dikuasai, progress per kategori, rekomendasi latihan);
  `PracticeService`: lifecycle sesi latihan (start/resume, attempt gesture,
  complete → skor & XP & streak, cek achievement otomatis);
  `GamificationService`: challenge harian (auto-create per tanggal, sekali
  attempt per student), unlock achievement berbasis threshold, leaderboard XP;
  `AssignmentService`: buat & bagikan tugas ke semua student, penyelesaian
  lewat skor sesi latihan (≥ min_score → completed + XP sekali);
  `TeacherStatsService`: statistik kelas guru (jumlah siswa, latihan minggu
  ini, rata-rata kelas, assignment aktif), monitoring per siswa (XP, level,
  streak, rata-rata skor, aktivitas terakhir), dan analisis error gesture
  dari `gesture_results` (gesture salah terbanyak per lesson/siswa);
  `MockGestureRecognitionService`: deteksi gesture mock (kontrak
  `GestureRecognitionService`, diganti AI nyata di Phase 9)
- `routes/web.php` — route `/student/*` (dashboard, materials, practice,
  progress) dan `/teacher/*` (dashboard, students, assignments)

## Testing

```bash
php artisan test
```

## Rencana Phase

1. ✅ Laravel setup + auth + role system
2. ✅ Database architecture (migrations, models, seeders)
3. ✅ Student module (dashboard, materials, progress, profile)
4. ✅ Practice module (session, gesture result, assessment)
5. ✅ Gamification (XP, level, streak, challenge, achievement)
6. ✅ Assignment
7. ✅ Teacher module (dashboard, monitoring, error analysis)
8. Reports (+ CSV export)
9. AI integration (AIRecognitionService + Python service)
10. Testing, security, optimization
