"""DatabaseSQLite untuk SignTeach.

Skema dibuat sesuai kebutuhan aplikasi saat ini (tanpa entitas berlebih).
Semua akses lewat helper `query` / `execute`.
DB tersimpan di file lokal `data/signteach.db` (dibuat otomatis).
"""

import hashlib
import os
import random
import sqlite3
from contextlib import closing
from datetime import date, datetime, timedelta

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DB_DIR = os.path.join(BASE_DIR, "data")
DB_PATH = os.path.join(DB_DIR, "signteach.db")

SCHEMA = """
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    name TEXT NOT NULL,
    role TEXT NOT NULL CHECK (role IN ('student', 'teacher')),
    created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS materials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category TEXT NOT NULL CHECK (category IN ('alfabet', 'angka', 'kosakata')),
    sign_system TEXT NOT NULL CHECK (sign_system IN ('SIBI', 'BISINDO')),
    title TEXT NOT NULL,
    description TEXT DEFAULT '',
    sort_order INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS lessons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    material_id INTEGER NOT NULL REFERENCES materials(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    target TEXT NOT NULL,
    practice_mode TEXT NOT NULL CHECK (practice_mode IN ('letter', 'word')),
    practice_target TEXT DEFAULT '',
    description TEXT DEFAULT '',
    sort_order INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS practice_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    material_id INTEGER REFERENCES materials(id),
    lesson_id INTEGER REFERENCES lessons(id),
    target TEXT NOT NULL,
    practice_mode TEXT NOT NULL,
    accuracy REAL DEFAULT 0,
    speed REAL DEFAULT 0,
    consistency REAL DEFAULT 0,
    completion REAL DEFAULT 0,
    final_score REAL DEFAULT 0,
    grade TEXT DEFAULT 'E',
    xp_earned INTEGER DEFAULT 0,
    duration_s REAL DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS gesture_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id INTEGER NOT NULL REFERENCES practice_sessions(id) ON DELETE CASCADE,
    seq INTEGER DEFAULT 0,
    expected TEXT NOT NULL,
    predicted TEXT NOT NULL,
    confidence REAL DEFAULT 0,
    is_correct INTEGER NOT NULL DEFAULT 0,
    feedback TEXT DEFAULT '',
    duration_ms INTEGER DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS badges (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    description TEXT DEFAULT ''
);

CREATE TABLE IF NOT EXISTS achievements (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    badge_id INTEGER NOT NULL REFERENCES badges(id) ON DELETE CASCADE,
    unlocked_at TEXT DEFAULT (datetime('now')),
    PRIMARY KEY (user_id, badge_id)
);

CREATE TABLE IF NOT EXISTS challenges (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    challenge_date TEXT NOT NULL,
    title TEXT NOT NULL,
    description TEXT DEFAULT '',
    category TEXT NOT NULL CHECK (category IN ('alfabet', 'angka', 'kosakata')),
    target INTEGER NOT NULL DEFAULT 1,
    reward_xp INTEGER NOT NULL DEFAULT 20,
    UNIQUE (challenge_date, category)
);

CREATE TABLE IF NOT EXISTS challenge_progress (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    challenge_id INTEGER NOT NULL REFERENCES challenges(id) ON DELETE CASCADE,
    progress INTEGER NOT NULL DEFAULT 0,
    completed INTEGER NOT NULL DEFAULT 0,
    completed_at TEXT,
    PRIMARY KEY (user_id, challenge_id)
);

CREATE TABLE IF NOT EXISTS assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    teacher_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    material_id INTEGER REFERENCES materials(id),
    title TEXT NOT NULL,
    description TEXT DEFAULT '',
    deadline TEXT,
    created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS assignment_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    assignment_id INTEGER NOT NULL REFERENCES assignments(id) ON DELETE CASCADE,
    student_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    status TEXT NOT NULL DEFAULT 'belum dimulai'
        CHECK (status IN ('belum dimulai', 'sedang dikerjakan', 'selesai')),
    completed_at TEXT,
    UNIQUE (assignment_id, student_id)
);

CREATE INDEX IF NOT EXISTS idx_sessions_user ON practice_sessions(user_id, created_at);
CREATE INDEX IF NOT EXISTS idx_results_session ON gesture_results(session_id);
"""


def _connect():
    os.makedirs(DB_DIR, exist_ok=True)
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA foreign_keys = ON")
    return conn


def query(sql, params=(), one=False):
    """SELECT helper -> list of sqlite3.Row (atau satu Row bila one=True)."""
    with closing(_connect()) as conn:
        cur = conn.execute(sql, params)
        rows = cur.fetchall()
    return (rows[0] if rows else None) if one else rows


def execute(sql, params=(), many=False):
    """INSERT/UPDATE/DELETE helper -> lastrowid."""
    with closing(_connect()) as conn:
        cur = conn.cursor()
        if many:
            cur.executemany(sql, params)
        else:
            cur.execute(sql, params)
        conn.commit()
        return cur.lastrowid


def hash_password(password: str) -> str:
    return hashlib.sha256(password.encode("utf-8")).hexdigest()


# ── Seeding ────────────────────────────────────────────────────────────

NUM_WORDS = {
    "1": "SATU", "2": "DUA", "3": "TIGA", "4": "EMPAT", "5": "LIMA",
    "6": "ENAM", "7": "TUJUH", "8": "DELAPAN", "9": "SEMBILAN",
}


def _seed_users():
    users = [
        ("guru@signteach.id", "guru123", "Budi Santoso", "teacher"),
        ("siswa@signteach.id", "siswa123", "Rina Putri", "student"),
        ("ahmad@signteach.id", "siswa123", "Ahmad Fauzi", "student"),
        ("dewi@signteach.id", "siswa123", "Dewi Lestari", "student"),
        ("bima@signteach.id", "siswa123", "Bimantara Jaya", "student"),
        ("siti@signteach.id", "siswa123", "Siti Nurhaliza", "student"),
    ]
    for username, pw, name, role in users:
        if not query("SELECT id FROM users WHERE username = ?", (username,), one=True):
            execute(
                "INSERT INTO users (username, password_hash, name, role) VALUES (?,?,?,?)",
                (username, hash_password(pw), name, role),
            )


def _seed_materials_and_lessons():
    material_rows = [
        ("alfabet", "SIBI", "Alfabet SIBI",
         "Alfabet isyarat A–Z Sistem Isyarat Bahasa Indonesia (SIBI) resmi.", 0),
        ("alfabet", "BISINDO", "Alfabet BISINDO",
         "Alfabet isyarat A–Z dengan ragam Bahasa Isyarat Indonesia (BISINDO).", 1),
        ("angka", "SIBI", "Angka SIBI",
         "Angka 1–10 SIBI. Dipraktikkan lewat ejaan abjad (contoh: SATU = S-A-T-U).", 2),
        ("angka", "BISINDO", "Angka BISINDO",
         "Angka 1–10 BISINDO dengan variasi regional.", 3),
        ("kosakata", "SIBI", "Kosakata Dasar SIBI",
         "Kata sehari-hari SIBI untuk komunikasi dasar.", 4),
        ("kosakata", "BISINDO", "Kosakata Dasar BISINDO",
         "Kata sehari-hari ragam BISINDO.", 5),
    ]
    for cat, system, title, desc, order in material_rows:
        if not query("SELECT id FROM materials WHERE title = ?", (title,), one=True):
            execute(
                "INSERT INTO materials (category, sign_system, title, description, sort_order) "
                "VALUES (?,?,?,?,?)",
                (cat, system, title, desc, order),
            )

    materials = {row["title"]: row for row in query("SELECT * FROM materials")}

    def add_lessons(material_title, rows):
        mid = materials[material_title]["id"]
        for i, (title, target, mode, practice_target, desc) in enumerate(rows):
            execute(
                "INSERT OR IGNORE INTO lessons "
                "(material_id, title, target, practice_mode, practice_target, description, sort_order) "
                "VALUES (?,?,?,?,?,?,?)",
                (mid, title, target, mode, practice_target, desc, i),
            )

    # Alfabet (SIBI & BISINDO): 26 huruf
    letters = [chr(c) for c in range(ord("A"), ord("Z") + 1)]
    for system in ("SIBI", "BISINDO"):
        title = "Alfabet SIBI" if system == "SIBI" else "Alfabet BISINDO"
        rows = [
            # (title, target, mode, practice_target, desc)
            (f"Huruf {ch}", ch, "letter", ch,
             f"Isyarat huruf {ch} ({system}). Posisi telapak tangan menghadap ke depan, jari-jari dibuka jelas.")
            for ch in letters
        ]
        add_lessons(title, rows)

    # Angka
    add_lessons("Angka SIBI", [
        (f"Angka {n}", n, "letter", NUM_WORDS[n],
         f"Isyarat angka {n} SIBI. Latihan: tunjukkan ejaan {NUM_WORDS[n]} bagian demi bagian.")
        for n in "123456789"
    ] + [("Angka 10", "10", "word", "SEPULUH",
          "Angka sepuluh SIBI. Latihan: ejaan S-E-P-U-L-U-H.")])

    add_lessons("Angka BISINDO", [
        (f"Angka {n}", n, "letter", NUM_WORDS[n],
         f"Angka {n} BISINDO. Latihan melalui ejaan {NUM_WORDS[n]}.")
        for n in "123456789"
    ])

    # Kosakata
    add_lessons("Kosakata Dasar SIBI", [
        ("SAYA", "SAYA", "word", "SAYA", "Mengarahkan telapak tangan ke dada. Ejaan S-A-Y-A."),
        ("MAKAN", "MAKAN", "word", "MAKAN", "Isyarat makan: tangan menuju mulut. Ejaan M-A-K-A-N."),
        ("MINUM", "MINUM", "word", "MINUM", "Seperti memegang gelas. Ejaan M-I-N-U-M."),
        ("PAGI", "PAGI", "word", "PAGI", "Sapaan waktu pagi. Ejaan P-A-G-I."),
        ("BELAJAR", "BELAJAR", "word", "BELAJAR", "Isyarat belajar/buku. Ejaan B-E-L-A-J-A-R."),
        ("TERIMA KASIH", "TERIMA KASIH", "word", "TERIMA KASIH",
         "Ejaan gabungan dua kata (spasi)."),
    ])

    add_lessons("Kosakata Dasar BISINDO", [
        ("SAYA", "SAYA", "word", "SAYA", "Menunjuk diri. Ejaan S-A-Y-A."),
        ("MAKAN", "MAKAN", "word", "MAKAN", "BISINDO: jari menutup lalu membuka dekat mulut."),
        ("MINUM", "MINUM", "word", "MINUM", "BISINDO: tangan memegang gelas imajiner."),
        ("PAGI", "PAGI", "word", "PAGI", "Ejaan P-A-G-I."),
    ])

    # Angka 0 tidak ada isyarat khusus -> skip (angka SIBI hanya 1..10)
    return materials


def _seed_badges():
    badges = [
        ("first_practice", "Langkah Pertama", "Selesaikan latihan pertamamu."),
        ("master_alfabet", "Master Alfabet", "10 latihan huruf dengan akurasi ≥ 90%."),
        ("master_angka", "Master Angka", "10 latihan angka dengan akurasi ≥ 90%."),
        ("sibi_explorer", "SIBI Explorer", "5 latihan materi SIBI selesai."),
        ("bisindo_explorer", "BISINDO Explorer", "5 latihan materi BISINDO selesai."),
        ("streak_7", "7 Day Streak", "Latihan 7 hari berturut-turut."),
        ("challenger", "Challenger", "Selesaikan 10 Challenge Harian."),
        ("perfect_round", "Putaran Sempurna", "Akurasi 100% dalam satu latihan."),
    ]
    for key, name, desc in badges:
        if not query("SELECT id FROM badges WHERE key = ?", (key,), one=True):
            execute("INSERT INTO badges (key, name, description) VALUES (?,?,?)", (key, name, desc))


def _seed_challenges():
    today = str(date.today())
    seed_rows = [
        ("Praktik 5 huruf", "Lakukan 5 latihan huruf alfabet hari ini.", "alfabet", 5),
        ("Praktik 3 kosakata", "Lakukan 3 latihan kata kosakata hari ini.", "kosakata", 3),
    ]
    for title, desc, category, target in seed_rows:
        if not query(
            "SELECT id FROM challenges WHERE challenge_date = ? AND category = ?",
            (today, category), one=True
        ):
            execute(
                "INSERT INTO challenges (challenge_date, title, description, category, target, reward_xp) "
                "VALUES (?,?,?,?,?,20)",
                (today, title, desc, category, target),
            )


def _seed_demo_history():
    """Riwayat latihan contoh untuk akun demo (siswa & guru)."""
    students = {
        row["username"]: row for row in query("SELECT * FROM users WHERE role = 'student'")
    }
    lessons = query("SELECT * FROM lessons")
    if not lessons:
        return

    active_users = ["siswa@signteach.id", "ahmad@signteach.id", "dewi@signteach.id", "bima@signteach.id"]
    rng = random.Random(42)
    now = datetime.now()

    for username in active_users:
        if query("SELECT id FROM practice_sessions WHERE user_id = ?",
                 (students[username]["id"],), one=True):
            continue
        uid = students[username]["id"]
        active_days = rng.sample(range(1, 15), rng.randint(5, 9)) if username == "siswa@signteach.id" \
            else rng.sample(range(1, 15), rng.randint(3, 8))

        for day_offset in active_days:
            n_sessions_today = rng.randint(2, 4) if username == "siswa@signteach.id" \
                else rng.randint(1, 3)
            for _ in range(n_sessions_today):
                lesson = rng.choice(lessons)
                mode = lesson["practice_mode"]
                target = lesson["practice_target"] or lesson["target"]
                if mode == "letter":
                    letters = [target]
                else:
                    letters = list(target.replace(" ", ""))

                n = len(letters)
                # probabilitas benar per huruf (dummy yang realistis)
                rng_acc = rng.uniform(0.62, 0.95)
                is_correct_list = [rng.random() < rng_acc for _ in range(n)]
                correct_n = sum(is_correct_list)
                confs = [round(rng.uniform(0.50, 0.97), 3) for _ in range(n)]
                duration = rng.uniform(8, 60)

                created_at = (now - timedelta(days=day_offset, hours=rng.randint(7, 20),
                                              minutes=rng.randint(0, 59))).strftime("%Y-%m-%d %H:%M:%S")

                # insertion order: session dulu
                acc = correct_n / max(1, n) * 100
                speed = min(100, max(40, round(100 - max(0, (duration / n - 2.5)) * 8, 1)))
                consistency = min(100, max(40, round(100 - (max(confs) - min(confs)) * 120, 1)))
                completion = 100.0
                final_score = round(acc * 0.4 + speed * 0.2 + consistency * 0.2 + completion * 0.2, 1)
                grade = "A" if final_score >= 90 else "B" if final_score >= 80 else \
                        "C" if final_score >= 70 else "D" if final_score >= 60 else "E"

                sid = execute(
                    "INSERT INTO practice_sessions "
                    "(user_id, material_id, lesson_id, target, practice_mode, "
                    " accuracy, speed, consistency, completion, final_score, grade, xp_earned, "
                    " duration_s, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    (uid, lesson["material_id"], lesson["id"], target, mode,
                     round(acc, 1), speed, consistency, completion, final_score, grade,
                     10, round(duration, 1), created_at),
                )

                for seq in range(n):
                    is_correct = is_correct_list[seq]
                    predicted = letters[seq] if is_correct else rng.choice(letters)
                    feedback = ("Gerakan sudah tepat" if is_correct
                                else "Posisi tangan kurang tepat")
                    execute(
                        "INSERT INTO gesture_results "
                        "(session_id, seq, expected, predicted, confidence, is_correct, feedback, duration_ms, created_at) "
                        "VALUES (?,?,?,?,?,?,?,?,?)",
                        (sid, seq, letters[seq], predicted, confs[seq], int(is_correct),
                         feedback, int(rng.uniform(1500, 6000)), created_at),
                    )


def _sync_badges_for_seeded_users():
    """Evaluasi badge untuk user seed yang punya riwayat latihan.

    evaluate_badges() hanya dipanggil setelah latihan live, sehingga akun
    demo ber-riwayat tidak punya badge sama sekali. Sinkronisasi idempotent
    ini (lihat _unlock yang cek duplikat) membuat achievement tampil
    konsisten dengan data demo. Import lokal untuk hindari circular import.
    """
    from .gamification import evaluate_badges

    rows = query(
        "SELECT DISTINCT user_id FROM practice_sessions s JOIN users u "
        "ON u.id = s.user_id WHERE u.role = 'student'"
    )
    for row in rows:
        evaluate_badges(row["user_id"])


def init_db(reset=False, seed=True):
    """Buat schema + data dasar. Idempotent (aman dipanggil tiap start)."""
    if reset and os.path.exists(DB_PATH):
        os.remove(DB_PATH)
    with closing(_connect()) as conn:
        conn.executescript(SCHEMA)
    if seed:
        _seed_users()
        _seed_materials_and_lessons()
        _seed_badges()
        _seed_challenges()
        _seed_demo_history()
        _sync_badges_for_seeded_users()