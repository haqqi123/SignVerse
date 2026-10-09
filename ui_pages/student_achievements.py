"""Achievement siswa — ringkasan + daftar badge unlocked & locked.

Data murni dari gamification existing (achievements, total_xp, streak_info,
badge_progress). Badge unlocked tampil lebih dulu (terbaru di atas), disusul
badge locked dengan indikator progres "x/target".
"""

import streamlit as st

from signlib import gamification, ui
from signlib.auth import require_role

require_role("student")
user = st.session_state["user"]
uid = user["id"]

# Ikon per badge (tampilan saja; key = badges.key di database)
BADGE_ICONS = {
    "first_practice": "🚀",
    "master_alfabet": "🔤",
    "master_angka": "🔢",
    "sibi_explorer": "📘",
    "bisindo_explorer": "📙",
    "streak_7": "🔥",
    "challenger": "🎯",
    "perfect_round": "💯",
}

ui.section_header("Achievement", "Kumpulkan semua badge! 🏆")

badges = gamification.achievements(uid)

if not badges:
    ui.empty_state("🏆", "Belum ada badge yang bisa dikumpulkan. Hubungi gurumu.")
    st.stop()

unlocked = [b for b in badges if b["unlocked"]]
locked = [b for b in badges if not b["unlocked"]]
unlocked.sort(key=lambda b: b["unlocked_at"] or "", reverse=True)

# ── Ringkasan ──────────────────────────────────────────────────────────
xp = gamification.total_xp(uid)
level = gamification.level_from_xp(xp)
streak = gamification.streak_info(uid)

c1, c2, c3, c4 = st.columns(4)
with c1:
    ui.stat_card("Badge Terbuka", f"{len(unlocked)}/{len(badges)}")
with c2:
    ui.stat_card("Total XP", f"{xp:,}")
with c3:
    ui.stat_card("Level", level["name"])
with c4:
    ui.stat_card("Streak", f'{streak["streak"]} hari 🔥')
st.markdown(ui.xp_bar_html(level["pct"], height=8), unsafe_allow_html=True)


def _badge_card(b, progress=None):
    """HTML kartu badge (komponen stat-card existing)."""
    icon = BADGE_ICONS.get(b["key"], "🏆" if b["unlocked"] else "🔒")
    base = (
        f'<div class="stat-card" style="text-align:center;margin-bottom:0.8rem'
        + ('' if b["unlocked"] else ';opacity:0.65') + '">'
        f'<div style="font-size:2rem">{icon}</div>'
        f'<div style="font-weight:800">{b["name"]}</div>'
        f'<div style="color:{ui.MUTED};font-size:0.85rem">{b["description"]}</div>'
    )
    if b["unlocked"]:
        return base + (
            f'<div style="color:{ui.SECONDARY};font-size:0.8rem;font-weight:600;'
            f'margin-top:0.3rem">✓ Unlocked {(b["unlocked_at"] or "")[:10]}</div>'
            f'</div>'
        )
    extra = ""
    if progress:
        cur, tgt = progress["current"], progress["target"]
        pct = min(100, int(cur / tgt * 100)) if tgt else 0
        extra = (
            '<div style="margin-top:0.4rem">'
            + ui.xp_bar_html(pct, height=6) +
            f'<div style="font-size:0.75rem;color:{ui.MUTED};margin-top:0.2rem">' 
            f'{cur}/{tgt}</div></div>'
        )
    return base + (
        f'<div style="font-size:0.8rem;color:{ui.MUTED};margin-top:0.3rem">🔒 Locked</div>'
        f'{extra}</div>'
    )


# ── Badge terbuka (terbaru dulu) ───────────────────────────────────────
if unlocked:
    ui.section_mini("Diraih", f'{len(unlocked)} badge terbuka')
    cols = st.columns(2)
    for i, b in enumerate(unlocked):
        with cols[i % 2]:
            st.markdown(_badge_card(b), unsafe_allow_html=True)

# ── Badge terkunci + progres ───────────────────────────────────────────
if locked:
    ui.section_mini("Menuju Badge Berikutnya", "Progres kamu untuk membuka badge")
    progress = gamification.badge_progress(uid)
    cols = st.columns(2)
    for i, b in enumerate(locked):
        with cols[i % 2]:
            st.markdown(_badge_card(b, progress.get(b["key"])), unsafe_allow_html=True)
