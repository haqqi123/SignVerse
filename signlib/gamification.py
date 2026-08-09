"""Gamifikasi SignTeach: XP, level, streak, dan badge."""

import datetime as _dt
from datetime import date

from . import db

_ONE_DAY = _dt.timedelta(days=1)

# ── Level ──────────────────────────────────────────────────────────────
LEVELS = [
    (0, "Beginner"),
    (300, "Explorer"),
    (900, "Communicator"),
    (2000, "Inclusive Champion"),
]

LEVEL_COLORS = {
    "Beginner": "#94a3b8",
    "Explorer": "#6366f1",
    "Communicator": "#06b6d4",
    "Inclusive Champion": "#f59e0b",
}


def level_from_xp(xp: int):
    current = LEVELS[0][1]
    idx = 0
    for i, (threshold, name) in enumerate(LEVELS):
        if xp >= threshold:
            current = name
            idx = i
    next_threshold = LEVELS[idx + 1][0] if idx + 1 < len(LEVELS) else None
    prev_threshold = LEVELS[idx][0]
    pct = 0.0
    if next_threshold is not None:
        pct = round((xp - prev_threshold) / (next_threshold - prev_threshold) * 100, 1)
    return {
        "name": current,
        "color": LEVEL_COLORS.get(current, "#6366f1"),
        "xp": xp,
        "threshold": prev_threshold,
        "next_xp": next_threshold,
        "pct": pct,
    }


def total_xp(user_id: int) -> int:
    row = db.query(
        "SELECT COALESCE(SUM(xp_earned),0) xp FROM practice_sessions WHERE user_id = ?",
        (user_id,), one=True,
    )
    bonus = db.query(
        "SELECT COALESCE(SUM(reward_xp),0) xp FROM challenge_progress cp "
        "JOIN challenges c ON c.id = cp.challenge_id "
        "WHERE cp.user_id = ? AND cp.completed = 1",
        (user_id,), one=True,
    )
    return int((row["xp"] if row else 0) + (bonus["xp"] if bonus else 0))


def streak_info(user_id: int) -> dict:
    """Hitung streak & last activity dari tanggal latihan user."""
    rows = db.query(
        "SELECT DISTINCT substr(created_at,1,10) d FROM practice_sessions "
        "WHERE user_id = ? ORDER BY d DESC",
        (user_id,),
    )
    days = [row["d"] for row in rows]
    if not days:
        return {"streak": 0, "last_activity": None, "is_today": False, "days": []}

    from datetime import datetime

    last = datetime.strptime(days[0], "%Y-%m-%d").date()
    today = date.today()

    streak = 0
    if last == today or last == today - _ONE_DAY:
        d = last
        while d.isoformat() in set(days):
            streak += 1
            d -= _ONE_DAY
    return {
        "streak": streak,
        "last_activity": days[0],
        "is_today": last == today,
        "days": days,
    }


# ── Badge ─────────────────────────────────────────────────────────────

def badge_catalog():
    return {row["key"]: row for row in db.query("SELECT * FROM badges")}


def unlocked_keys(user_id: int) -> set:
    rows = db.query(
        "SELECT b.key FROM achievements a JOIN badges b ON b.id = a.badge_id WHERE a.user_id = ?",
        (user_id,),
    )
    return {row["key"] for row in rows}


def _unlock(user_id, badge_key):
    badge = badge_catalog().get(badge_key)
    if not badge:
        return False
    exists = db.query(
        "SELECT 1 FROM achievements WHERE user_id = ? AND badge_id = ?",
        (user_id, badge["id"]), one=True,
    )
    if exists:
        return False
    db.execute(
        "INSERT INTO achievements (user_id, badge_id) VALUES (?,?)",
        (user_id, badge["id"]),
    )
    return True


def evaluate_badges(user_id: int):
    """Evaluasi unlock badge berdasarkan data latihan (dipanggil setelah latihan)."""
    cat_stats = {}
    for row in db.query(
        "SELECT m.category category, m.sign_system system, COUNT(*) n, AVG(s.accuracy) acc "
        "FROM practice_sessions s JOIN materials m ON m.id = s.material_id "
        "WHERE s.user_id = ? GROUP BY m.category, m.sign_system",
        (user_id,),
    ):
        cat_stats[(row["category"], row["system"])] = dict(row)

    def stat_cat(cat):
        """Agregasi satu kategori lintas sistem isyarat (SIBI + BISINDO)."""
        rows = [v for k, v in cat_stats.items() if k[0] == cat]
        n = sum(r["n"] for r in rows)
        acc = (sum(r["acc"] * r["n"] for r in rows) / n) if n else 0
        return {"n": n, "acc": acc}

    alfabet = stat_cat("alfabet")
    angka = stat_cat("angka")

    streak = streak_info(user_id)["streak"]
    challenges_done = db.query(
        "SELECT COUNT(*) n FROM challenge_progress WHERE user_id = ? AND completed = 1",
        (user_id,), one=True,
    )["n"]

    checks = {
        "first_practice": db.query(
            "SELECT COUNT(*) n FROM practice_sessions WHERE user_id = ?", (user_id,), one=True)["n"] >= 1,
        "master_alfabet": alfabet["n"] >= 10 and alfabet["acc"] >= 90,
        "master_angka": angka["n"] >= 10 and angka["acc"] >= 90,
        "sibi_explorer": _count_sign(user_id, "SIBI") >= 5,
        "bisindo_explorer": _count_sign(user_id, "BISINDO") >= 5,
        "streak_7": streak >= 7,
        "challenger": challenges_done >= 10,
        "perfect_round": db.query(
            "SELECT COUNT(*) n FROM practice_sessions WHERE user_id = ? AND accuracy >= 100",
            (user_id,), one=True)["n"] >= 1,
    }
    for badge_key, unlocked in checks.items():
        if unlocked:
            _unlock(user_id, badge_key)


def _count_sign(user_id, sign):
    return db.query(
        "SELECT COUNT(*) n FROM practice_sessions s JOIN materials m ON m.id = s.material_id "
        "WHERE s.user_id = ? AND m.sign_system = ?",
        (user_id, sign), one=True,
    )["n"]


def achievements(user_id: int):
    """List semua badge (unlocked/locked) plus tanggal unlock."""
    catalog = badge_catalog()
    unlocks = db.query(
        "SELECT b.key, a.unlocked_at FROM achievements a "
        "JOIN badges b ON b.id = a.badge_id WHERE a.user_id = ?",
        (user_id,),
    )
    unlock_map = {row["key"]: row["unlocked_at"] for row in unlocks}
    result = []
    for key, badge in catalog.items():
        result.append({
            "key": key,
            "name": badge["name"],
            "description": badge["description"],
            "unlocked": key in unlock_map,
            "unlocked_at": unlock_map.get(key),
        })
    return result