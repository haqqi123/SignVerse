"""Achievement siswa — daftar badge unlocked & locked."""

import streamlit as st

from signlib import gamification, ui
from signlib.auth import require_role

require_role("student")
user = st.session_state["user"]
uid = user["id"]

ui.section_header("Achievement", "Kumpulkan semua badge! 🏆")

xp = gamification.total_xp(uid)
level = gamification.level_from_xp(xp)
st.markdown(
    f'<div class="stat-card" style="margin-bottom:1rem">'
    f'<div style="font-weight:800;color:{level["color"]}">Level {level["name"]}</div>'
    f'<div style="color:{ui.MUTED};font-size:0.85rem">{xp:,} XP</div>'
    + ui.xp_bar_html(level["pct"], height=8) +
    f'</div>',
    unsafe_allow_html=True,
)

badges = gamification.achievements(uid)
cols = st.columns(2)
for i, b in enumerate(badges):
    with cols[i % 2]:
        if b["unlocked"]:
            st.markdown(
                f'<div class="stat-card" style="text-align:center;margin-bottom:0.8rem">'
                f'<div style="font-size:2rem">🏆</div>'
                f'<div style="font-weight:800">{b["name"]}</div>'
                f'<div style="color:{ui.MUTED};font-size:0.85rem">{b["description"]}</div>'
                f'<div style="color:{ui.SECONDARY};font-size:0.8rem;font-weight:600;'
                f'margin-top:0.3rem">✓ Unlocked {b["unlocked_at"][:10]}</div>'
                f'</div>',
                unsafe_allow_html=True,
            )
        else:
            st.markdown(
                f'<div class="stat-card" style="text-align:center;margin-bottom:0.8rem;'
                f'opacity:0.65">'
                f'<div style="font-size:2rem">🔒</div>'
                f'<div style="font-weight:800">{b["name"]}</div>'
                f'<div style="color:{ui.MUTED};font-size:0.85rem">{b["description"]}</div>'
                f'<div style="font-size:0.8rem;color:{ui.MUTED};margin-top:0.3rem">Locked</div>'
                f'</div>',
                unsafe_allow_html=True,
            )