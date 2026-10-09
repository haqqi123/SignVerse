# SignTeach Laravel Web Application 🤟

Aplikasi utama SignTeach berbasis **Laravel 11** untuk platform pembelajaran bahasa isyarat SIBI dan BISINDO.

---

## 🛠️ Persyaratan Lingkungan
- **PHP**: >= 8.2 (direkomendasikan PHP 8.3)
- **Composer**: >= 2.0
- **Database**: SQLite (default) atau MySQL / PostgreSQL
- **FastAPI AI Service**: Berjalan di `http://127.0.0.1:8100` untuk inferensi kamera deteksi isyarat.

---

## 🚀 Panduan Menjalankan

1. **Install dependensi PHP:**
   ```bash
   composer install
   ```

2. **Migrasi Database & Data Awal (Seed):**
   ```bash
   php artisan migrate --seed
   ```

3. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Aplikasi akan aktif di [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 🧪 Eksekusi Testing Suite

Seluruh logika bisnis, otentikasi role, alur latihan, penugasan, dan pelaporan diuji secara otomatis dengan PHPUnit:

```bash
php artisan test
```

Semua 47 pengujian mencakup:
- `AuthFlowTest`: Otentikasi, login role guard, dan registrasi siswa.
- `NavigationL1Test`: Validasi 15 rute navigasi siswa & guru.
- `ServicesPhase1Test`: Validasi logika data service layer (Learning, Student, Challenge, Assignment, Teacher).
- `StudentPagesTest`: Tampilan dashboard, katalog materi, achievement, challenge, progress, dan profil siswa.
- `PracticeFlowTest`: Alur latihan live kamera dan API penilaian Smart Assessment (`POST /student/practice/submit`).
- `StudentAssignmentTest`: Alur pengerjaan dan auto-complete tugas siswa.
- `TeacherFlowTest`: Dasbor analitik kelas, monitoring mendalam, pembuatan tugas, dan ekspor laporan CSV.

---

## 🔑 Akun Demo
- **Siswa**: `siswa@signteach.id` / `password`
- **Guru**: `guru@signteach.id` / `password`
