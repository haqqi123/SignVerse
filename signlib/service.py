"""Lapisan service: query data aplikasi (dashboard, progress, guru).

Semua perhitungan agregat berada di sini, bukan di halaman.
"""

from datetime import date, datetime, timedelta

from . import db


# ── Materi ────────────────────────────────────────────────────────────

def get_materials():
    return db.query("SELECT * FROM materials ORDER BY sort_order")


def get_materials_by_category(category):
    return db.query(
        "SELECT * FROM materials WHERE category = ? ORDER BY sign_system, sort_order",
        (category,),
    )


def get_lessons(material_id):
    return db.query(
        "SELECT * FROM lessons WHERE material_id = ? ORDER BY sort_order",
        (material_id,),
    )


def get_lesson(lesson_id):
    return db.query("SELECT * FROM lessons WHERE id = ?", (lesson_id,), one=True)


def get_material(material_id):
    return db.query("SELECT * FROM materials WHERE id = ?", (material_id,), one=True)


# ── Statistik siswa ────────────────────────────────────────────────────

def student_stats(user_id):
    """Statistik inti untuk dashboard siswa."""
    total_sessions = db.query(
        "SELECT COUNT(*) n FROM practice_sessions WHERE user_id = ?", (user_id,), one=True)["n"]
    avg_acc = db.query(
        "SELECT AVG(accuracy) a FROM practice_sessions WHERE user_id = ?", (user_id,), one=True)["a"] or 0
    materials_done = db.query(
        "SELECT COUNT(DISTINCT material_id) n FROM practice_sessions WHERE user_id = ?",
        (user_id,), one=True)["n"]
    challenges_done = db.query(
        "SELECT COUNT(*) n FROM challenge_progress WHERE user_id = ? AND completed = 1",
        (user_id,), one=True)["n"]
    return {
        "total_sessions": total_sessions,
        "avg_accuracy": round(avg_acc, 1),
        "materials_done": materials_done,
        "challenges_done": challenges_done,
    }


def category_accuracy(user_id):
    """Akurasi rata-rata per kategori (untuk rekomendasi & progres)."""
    rows = db.query(
        "SELECT m.category, AVG(s.accuracy) acc, COUNT(*) n "
        "FROM practice_sessions s JOIN materials m ON m.id = s.material_id "
        "WHERE s.user_id = ? GROUP BY m.category",
        (user_id,),
    )
    return {row["category"]: {"accuracy": round(row["acc"], 1), "count": row["n"]} for row in rows}


def last_lesson(user_id):
    """Materi terakhir dipelajari (Continue Learning)."""
    return db.query(
        "SELECT s.*, l.title lesson_title, l.target, m.title material_title, m.category "
        "FROM practice_sessions s "
        "LEFT JOIN lessons l ON l.id = s.lesson_id "
        "LEFT JOIN materials m ON m.id = s.material_id "
        "WHERE s.user_id = ? ORDER BY s.created_at DESC LIMIT 1",
        (user_id,), one=True,
    )


def accuracy_trend(user_id, days=7):
    """Rata-rata akurasi per hari (untuk grafik Progress)."""
    rows = db.query(
        "SELECT substr(created_at,1,10) d, AVG(accuracy) acc "
        "FROM practice_sessions WHERE user_id = ? AND created_at >= datetime('now', ?) "
        "GROUP BY d ORDER BY d",
        (user_id, f"-{days} days"),
    )
    return [{"day": r["d"], "accuracy": round(r["acc"], 1)} for r in rows]


def practice_history(user_id, limit=30):
    return db.query(
        "SELECT s.*, m.title material_title, l.title lesson_title "
        "FROM practice_sessions s "
        "LEFT JOIN materials m ON m.id = s.material_id "
        "LEFT JOIN lessons l ON l.id = s.lesson_id "
        "WHERE s.user_id = ? ORDER BY s.created_at DESC LIMIT ?",
        (user_id, limit),
    )


def gesture_errors(user_id):
    """Analisis kesalahan berdasar hasil deteksi nyata."""
    rows = db.query(
        "SELECT expected, COUNT(*) n, "
        "AVG(is_correct) * 100 acc, SUM(is_correct) correct, COUNT(*) - SUM(is_correct) wrong "
        "FROM gesture_results gr JOIN practice_sessions s ON s.id = gr.session_id "
        "WHERE s.user_id = ? GROUP BY expected HAVING COUNT(*) > 0 ORDER BY acc ASC",
        (user_id,),
    )
    return [dict(row) for row in rows]


# ── Challenge ──────────────────────────────────────────────────────────

def today_challenges():
    return db.query(
        "SELECT * FROM challenges WHERE challenge_date = ? ORDER BY id",
        (str(date.today()),),
    )


def challenge_status(user_id):
    rows = db.query(
        "SELECT c.*, cp.progress progress, cp.completed completed "
        "FROM challenges c "
        "LEFT JOIN challenge_progress cp ON cp.challenge_id = c.id AND cp.user_id = ? "
        "WHERE c.challenge_date = ? ORDER BY c.id",
        (user_id, str(date.today())),
    )
    return rows


def bump_challenge(user_id, category, completed_all=False):
    """Progress challenge harian +reward otomatis saat target tercapai."""
    rows = db.query(
        "SELECT c.*, cp.progress progress, cp.completed completed FROM challenges c "
        "LEFT JOIN challenge_progress cp ON cp.challenge_id = c.id AND cp.user_id = ? "
        "WHERE c.challenge_date = ? AND c.category = ?",
        (user_id, str(date.today()), category),
    )
    unlocked_any = False
    for ch in rows:
        cur = (ch["progress"] or 0) + 1 if not completed_all else ch["target"]
        if ch["progress"] is None:
            db.execute(
                "INSERT INTO challenge_progress (user_id, challenge_id, progress, completed) VALUES (?,?,0,0)",
                (user_id, ch["id"]),
            )
        completed = 1 if cur >= ch["target"] else 0
        db.execute(
            "UPDATE challenge_progress SET progress = ?, completed = ?, "
            "completed_at = CASE WHEN ? THEN datetime('now') ELSE completed_at END "
            "WHERE user_id = ? AND challenge_id = ?",
            (cur, completed, completed, user_id, ch["id"]),
        )
        if completed and not ch["completed"]:
            reward_challenge_xp(user_id, ch)
            unlocked_any = True
    return unlocked_any


def reward_challenge_xp(user_id, challenge_row):
    """Simpan bonus XP challenge (computed via total_xp yang terdiri dari
    sessions + challenge sehingga challenger XP masuk ke total_xp)."""
    # Total XP dihitung dari SUM(xp_earned sessions) + SUM(reward_xp challenge)
    # jadi tidak ada kolom tambahan yang perlu diupdate.
    pass


def challenge_rewards(user_id):
    return db.query(
        "SELECT c.title, c.reward_xp, cp.completed_at FROM challenge_progress cp "
        "JOIN challenges c ON c.id = cp.challenge_id "
        "WHERE cp.user_id = ? AND cp.completed = 1 ORDER BY cp.completed_at DESC",
        (user_id,),
    )


# ── Guru ───────────────────────────────────────────────────────────────

def all_students():
    return db.query(
        "SELECT u.id, u.name, u.username, COALESCE(COUNT(s.id), 0) AS n_sessions "
        "FROM users u LEFT JOIN practice_sessions s ON s.user_id = u.id "
        "WHERE u.role = 'student' GROUP BY u.id ORDER BY u.name",
    )


def teacher_summary():
    students = all_students()
    total_students = len(students)
    avg_score = db.query(
        "SELECT AVG(final_score) a FROM practice_sessions", one=True)["a"] or 0
    weekly = db.query(
        "SELECT COUNT(*) n FROM practice_sessions WHERE created_at >= datetime('now', '-7 days')",
        one=True,
    )["n"]
    active_assignments = db.query(
        "SELECT COUNT(*) n FROM assignments WHERE deadline >= date('now')",
        one=True,
    )["n"]
    return {
        "students": total_students,
        "avg_score": round(avg_score, 1),
        "weekly_sessions": weekly,
        "active_assignments": active_assignments,
    }


def student_detail(user_id):
    """Detail lengkap satu siswa (untuk halaman monitoring guru)."""
    stats = student_stats(user_id)
    profile = db.query(
        "SELECT u.id, u.name, u.username, u.created_at FROM users u WHERE u.id = ?",
        (user_id,), one=True,
    )
    wins = category_accuracy(user_id)
    errors = gesture_errors(user_id)
    last = db.query(
        "SELECT substr(MAX(created_at),1,10) d FROM practice_sessions WHERE user_id = ?",
        (user_id,), one=True,
    )
    return {
        **dict(profile),
        "stats": stats,
        "category_accuracy": wins,
        "errors": errors[:5],
        "last_activity": last["d"] if last else None,
        "history": practice_history(user_id, 10),
        "achievements": None,  # diisi oleh pemanggil (perlu gamification)
    }


def class_report():
    students = all_students()
    report = []
    for s in students:
        row = db.query(
            "SELECT AVG(accuracy) acc, AVG(final_score) score, COUNT(*) n, "
            "COUNT(DISTINCT material_id) materials FROM practice_sessions WHERE user_id = ?",
            (s["id"],), one=True,
        )
        last = db.query(
            "SELECT MAX(created_at) d FROM practice_sessions WHERE user_id = ?",
            (s["id"],), one=True,
        )
        report.append({
            "id": s["id"],
            "name": s["name"],
            "accuracy": round(row["acc"] or 0, 1),
            "score": round(row["score"] or 0, 1),
            "sessions": row["n"],
            "materials": row["materials"] or 0,
            "last_activity": (last["d"] or "")[:10],
        })
    return report


def weekly_activity(days=7):
    rows = db.query(
        "SELECT substr(created_at,1,10) d, COUNT(*) n FROM practice_sessions "
        "WHERE created_at >= datetime('now', ?) GROUP BY d ORDER BY d",
        (f"-{days} days",),
    )
    return [dict(r) for r in rows]


# ── Assignment ─────────────────────────────────────────────────────────

def create_assignment(teacher_id, material_id, title, description, student_ids, deadline):
    last = db.execute(
        "INSERT INTO assignments (teacher_id, material_id, title, description, deadline) "
        "VALUES (?,?,?,?,?)",
        (teacher_id, material_id, title, description, deadline),
    )
    for sid in student_ids:
        db.execute(
            "INSERT OR IGNORE INTO assignment_items (assignment_id, student_id, status) VALUES (?,?, 'belum dimulai')",
            (last, sid),
        )
    return last


def teacher_assignments(teacher_id):
    return db.query(
        "SELECT a.*, m.title material_title, "
        "(SELECT COUNT(*) FROM assignment_items ai WHERE ai.assignment_id = a.id) total_students, "
        "(SELECT COUNT(*) FROM assignment_items ai WHERE ai.assignment_id = a.id AND ai.status = 'selesai') done "
        "FROM assignments a LEFT JOIN materials m ON m.id = a.material_id "
        "WHERE a.teacher_id = ? ORDER BY a.created_at DESC",
        (teacher_id,),
    )


def assignment_detail(assignment_id):
    return db.query(
        "SELECT a.*, m.title material_title, u.name teacher_name FROM assignments a "
        "LEFT JOIN materials m ON m.id = a.material_id "
        "JOIN users u ON u.id = a.teacher_id WHERE a.id = ?",
        (assignment_id,), one=True,
    )


def assignment_students(assignment_id):
    return db.query(
        "SELECT ai.*, u.name FROM assignment_items ai JOIN users u ON u.id = ai.student_id "
        "WHERE ai.assignment_id = ? ORDER BY u.name",
        (assignment_id,),
    )


def student_assignments(user_id):
    """List assignment yang ditujukan ke satu siswa (+ status)."""
    return db.query(
        "SELECT a.*, m.title material_title, ai.status, ai.completed_at "
        "FROM assignment_items ai "
        "JOIN assignments a ON a.id = ai.assignment_id "
        "LEFT JOIN materials m ON m.id = a.material_id "
        "WHERE ai.student_id = ? ORDER BY a.created_at DESC",
        (user_id,),
    )


def update_assignment_status(user_id, assignment_id, status):
    db.execute(
        "UPDATE assignment_items SET status = ?, "
        "completed_at = CASE WHEN ? = 'selesai' THEN datetime('now') ELSE completed_at END "
        "WHERE student_id = ? AND assignment_id = ?",
        (status, status, user_id, assignment_id),
    )


def first_lesson(material_id):
    """Lesson pertama dari sebuah materi (untuk loncat langsung ke latihan)."""
    return db.query(
        "SELECT id FROM lessons WHERE material_id = ? ORDER BY sort_order LIMIT 1",
        (material_id,), one=True,
    )


def complete_assignments_for_material(user_id, material_id):
    """Tuntaskan assignment berstatus 'sedang dikerjakan' untuk materi tsb.

    Dipanggil setelah siswa menyelesaikan satu sesi latihan materi yang
    sama dengan assignment-nya, sehingga alur kerja-tugas menjadi otomatis.
    Return: daftar judul assignment yang baru saja selesai.
    """
    if not material_id:
        return []
    rows = db.query(
        "SELECT a.id, a.title FROM assignment_items ai "
        "JOIN assignments a ON a.id = ai.assignment_id "
        "WHERE ai.student_id = ? AND a.material_id = ? AND ai.status = 'sedang dikerjakan'",
        (user_id, material_id),
    )
    titles = []
    for r in rows:
        db.execute(
            "UPDATE assignment_items SET status = 'selesai', "
            "completed_at = datetime('now') WHERE student_id = ? AND assignment_id = ?",
            (user_id, r["id"]),
        )
        titles.append(r["title"])
    return titles

