# SignTeach 🤟 — Platform Pembelajaran Bahasa Isyarat Indonesia

SignTeach adalah platform edukasi interaktif untuk mempelajari bahasa isyarat **SIBI** (*Sistem Isyarat Bahasa Indonesia*) dan **BISINDO** (*Bahasa Isyarat Indonesia*) yang dilengkapi dengan deteksi gestur real-time berbasis AI (**YOLOv8**), sistem penilaian cerdas (*Smart Assessment*), dan gamifikasi belajar.

Project ini telah berhasil dimigrasikan secara penuh dari prototipe Streamlit ke arsitektur full-stack modern berbasis **Laravel 11** dan microservice **FastAPI**.

---

## 🏛️ Arsitektur Aplikasi

```
                   ┌────────────────────────────────────────┐
                   │           Browser (Pengguna)           │
                   └───────┬────────────────────────┬───────┘
                           │ HTTP / Web             │ Frame Kamera (POST)
                           ▼                        ▼
           ┌─────────────────────────────┐   ┌─────────────────────────────┐
           │      Laravel 11 App         │   │     AI Microservice         │
           │      (Port 8000 / 8012)     │   │      (Port 8100)            │
           │                             │   │                             │
           │  • Blade UI & Styling       │   │  • FastAPI + Uvicorn        │
           │  • Auth & Role Guard        │   │  • Model YOLOv8 (best.pt)   │
           │  • Student & Teacher Module │   │  • Parameter deteksi identik│
           │  • Smart Assessment Engine  │   │  • CORS Enabled             │
           │  • Gamification & Streaks   │   └─────────────────────────────┘
           │  • SQLite Database (9 tabel)│
           └─────────────────────────────┘
```

---

## 🚀 Panduan Menjalankan Aplikasi

Aplikasi membutuhkan dua terminal yang berjalan bersamaan:

### Terminal 1: AI Microservice (FastAPI + YOLOv8)
Microservice ini bertugas memproses deteksi gestur alfabet tangan dari frame kamera web secara real-time.

```bash
cd ai-service
pip install -r requirements.txt
uvicorn main:app --host 127.0.0.1 --port 8100
```
> Pastikan status service aktif di: [http://127.0.0.1:8100/health](http://127.0.0.1:8100/health) (harus menampilkan status `"ok"` dan model `"best.pt"`).

---

### Terminal 2: Web Application (Laravel 11)
Aplikasi utama SignTeach yang menangani antarmuka pengguna, sistem akun, penugasan, kurikulum materi, dan gamifikasi.

```bash
cd laravel-app
composer install
php artisan migrate --seed
php artisan serve
```
> Buka aplikasi di peramban: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🔑 Akun Demo Bawaan (Seeded Accounts)

Gunakan akun demo berikut untuk langsung menguji fungsionalitas:

| Role | Username / Email | Password | Hak Akses & Fitur |
|---|---|---|---|
| **Siswa** | `siswa@signteach.id` | `password` | Dashboard, Materi, AI Practice Kamera, Tantangan Harian, Progres, Achievement, Tugas Siswa, Komunikasi Inklusif |
| **Guru** | `guru@signteach.id` | `password` | Dashboard Kelas, Daftar Siswa, Monitoring Analitik & Kesalahan Gestur, Manajemen Tugas, Laporan & Ekspor CSV |

---

## ✨ Fitur-Fitur Utama

### 1. Modul Siswa (`/student/*`)
- **Dashboard Siswa**: 4 kartu ringkasan (XP, Level, Streak, Akurasi), kartu *Continue Learning* untuk melanjutkan materi terakhir, rekomendasi materi AI, dan widget tantangan harian.
- **Katalog Materi Belajar**: Filter kategori (Alfabet, Angka, Kosakata) dan sistem isyarat (SIBI & BISINDO) lengkap dengan daftar lesson.
- **AI Practice Room**:
  - Live webcam feed HTML5 `<video>` & canvas overlay.
  - Deteksi real-time huruf alfabet A–Z via FastAPI `ai-service`.
  - Smart Assessment: Akurasi (%), Kecepatan (Speed), Konsistensi (Consistency), dan Tingkat Penyelesaian (Completion) yang menghasilkan Grade (A–E) dan poin XP.
  - Dilengkapi tombol simulasi gestur untuk uji coba tanpa webcam.
- **Challenge Harian**: Misi latihan harian dengan progress bar dan reward XP langsung.
- **Progress Belajar**: Grafik tren akurasi 7 hari, tingkat penguasaan per topik materi, tabel analisis kesalahan gestur, dan histori 25 sesi latihan terakhir.
- **Achievement & Badges**: 8 badge pencapaian (diraih & terkunci dengan progress bar target `x/target`).
- **Modul Assignment Siswa**: Daftar tugas dari guru dengan alur *auto-complete* otomatis saat latihan materi terkait selesai dikerjakan.
- **Inclusive Communication**:
  - *Sign-to-Voice*: Deteksi isyarat kamera menyusun kata + Text-to-Speech (TTS) suara berbahasa Indonesia.
  - *Voice-to-Sign*: Menerjemahkan ucapan mikrofon (*Speech Recognition*) atau teks menjadi kartu ejaan isyarat (*fingerspelling*).

### 2. Modul Guru (`/teacher/*`)
- **Dashboard Guru**: Metrik kelas (total siswa, rata-rata skor kelas, sesi aktif mingguan, tugas berjalan, dan grafik aktivitas 7 hari).
- **Daftar Siswa**: Tabel performa seluruh siswa (skor, akurasi, sesi latihan, materi selesai, dan tanggal terakhir aktif).
- **Monitoring Mendalam**: Analisis kesalahan gestur spesifik siswa (*Error Analysis*), capaian badge, dan riwayat sesi latihan.
- **Manajemen Assignment**: Formulir pembuatan tugas baru (pilih materi, tenggat waktu, checklist siswa) serta pelacakan progres penyelesaian kelas.
- **Laporan & Ekspor**: Rekapitulasi nilai kelas dengan tombol unduh **Ekspor CSV** berstandar UTF-8 BOM.

---

## 🧪 Pengujian Otomatis (Automated Testing)

Aplikasi memiliki test suite PHPUnit komprehensif yang mencakup seluruh alur kerja:

```bash
cd laravel-app
php artisan test
```

**Hasil Pengujian:**
- Total test: **47 tests**
- Total assertions: **209 assertions**
- Status: **100% Passed (0 Failures, 0 Errors)**
