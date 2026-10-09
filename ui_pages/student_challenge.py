"""Challenge Harian siswa — progress & reward."""

import streamlit as st

from signlib import flow, gamification, service, ui
from signlib.auth import require_role

require_role("student")
user = st.session_state["user"]
uid = user["id"]

ui.section_header("Challenge Harian", "Selesaikan tantangan dan raih XP tambahan")

challenges = service.challenge_status(uid)
if challenges:
    for ch in challenges:
        prog = ch["progress"] or 0
        pct = min(100, int(prog / ch["target"] * 100)) if ch["target"] else 0
        done = " ✅ Selesai!" if ch["completed"] else ""
        st.markdown(
            f'<div class="stat-card" style="margin-bottom:0.8rem">'
            f'<div style="font-weight:800;font-size:1.05rem">{ch["title"]}{done}</div>'
            f'<div style="color:{ui.MUTED};font-size:0.85rem">{ch["description"]}</div>'
            f'<div style="color:{ui.MUTED};font-size:0.9rem;margin:0.4rem 0">'
            f'Progress: {prog}/{ch["target"]} · Reward: +{ch["reward_xp"]} XP</div>'
            + ui.xp_bar_html(pct, height=8) +
            (f'<div style="color:{ui.SECONDARY};font-weight:700;margin-top:0.4rem">'
             f'Reward +{ch["reward_xp"]} XP diberikan! 🎉</div>'
             if ch["completed"] else "") +
            f'</div>',
            unsafe_allow_html=True,
        )
else:
    st.info("Tidak ada challenge hari ini. Coba lagi besok.")

st.divider()
st.markdown(
    f'<div class="stat-card" style="text-align:center">'
    f'<div style="font-weight:800">Streak Kamu</div>'
    f'<div style="font-size:2rem;font-weight:800">{gamification.streak_info(uid)["streak"]} '
    f'🔥 hari</div>'
    f'<div style="color:{ui.MUTED};font-size:0.85rem">'
    f'Latihan hari ini akan menjaga streak tetap berjalan.</div>'
    f'</div>',
    unsafe_allow_html=True,
)

st.divider()
ui.section_mini("Reward yang Sudah Diraih", "Riwayat reward challenge")
rewards = service.challenge_rewards(uid)
if rewards:
    for r in rewards:
        st.markdown(
            f'<div class="stat-card" style="padding:0.6rem 0.9rem;margin-bottom:0.4rem">'
            f'<span style="font-weight:700">+{r["reward_xp"]} XP</span> — {r["title"]} '
            f'<span style="color:{ui.MUTED};font-size:0.8rem">{r["completed_at"][:16]}</span>'
            f'</div>',
            unsafe_allow_html=True,
        )
else:
    st.caption("Selesaikan challenge hari ini untuk melihat riwayat reward.")

def _start_challenge_lesson():
    """Langsung buka latihan materi yang sesuai kategori challenge."""
    challenges = service.challenge_status(uid)
    category = challenges[0]["category"] if challenges else None
    lesson_id = None
    if category:
        materials = service.get_materials_by_category(category)
        if materials:
            lesson = service.first_lesson(materials[0]["id"])
            lesson_id = lesson["id"] if lesson else None
    flow.start_practice(lesson_id)
    st.switch_page("ui_pages/student_practice.py")


if st.button("Latihan Sekarang untuk Challenge 🔥", type="primary"):
    _start_challenge_lesson()