"""Profil siswa — info akun + ringkasan + logout."""

import streamlit as st

from signlib import gamification, service, ui
from signlib.auth import logout, require_role

require_role("student")
user = st.session_state["user"]
uid = user["id"]

ui.section_header("Profil Saya", "Ringkasan akun dan aktivitas belajar")

c1, c2 = st.columns([1, 2])
with c1:
    st.markdown(
        f'<div class="stat-card" style="text-align:center;padding:1.6rem">'
        f'<div style="font-size:3rem">👤</div>'
        f'<div style="font-weight:800;font-size:1.2rem">{user["name"]}</div>'
        f'<div style="color:{ui.MUTED}">{user["username"]}</div>'
        f'<div style="margin-top:0.6rem">{ui.badge("Siswa", "indigo")}</div>'
        f'</div>',
        unsafe_allow_html=True,
    )
    if st.button("Keluar", type="secondary", use_container_width=True):
        logout()
        st.rerun()
with c2:
    stats = service.student_stats(uid)
    xp = gamification.total_xp(uid)
    level = gamification.level_from_xp(xp)
    streak = gamification.streak_info(uid)

    st.markdown(
        f'<div class="stat-card">'
        f'<div style="font-weight:800;color:{level["color"]}">Level {level["name"]}</div>'
        f'<div style="color:{ui.MUTED};font-size:0.85rem">{xp:,} XP total</div>'
        + ui.xp_bar_html(level["pct"], height=10) +
        f'</div>',
        unsafe_allow_html=True,
    )
    m1, m2, m3, m4 = st.columns(4)
    with m1:
        ui.stat_card("Latihan", stats["total_sessions"])
    with m2:
        ui.stat_card("Akurasi", f'{stats["avg_accuracy"]:.0f}%')
    with m3:
        ui.stat_card("Streak", f'{streak["streak"]}')
    with m4:
        ui.stat_card("Materi", stats["materials_done"])
