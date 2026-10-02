"""Dashboard Siswa — overview pembelajaran."""

from datetime import datetime

import streamlit as st

from signlib import flow, gamification, recommendation, service, ui
from signlib.auth import require_role

require_role("student")
user = st.session_state["user"]
uid = user["id"]

stats = service.student_stats(uid)
streak = gamification.streak_info(uid)
xp = gamification.total_xp(uid)
level = gamification.level_from_xp(xp)
last = service.last_lesson(uid)
recommend = recommendation.next_recommendation(uid)
challenges = service.challenge_status(uid)
achievements = gamification.achievements(uid)
newest_unlocked = [a for a in achievements if a["unlocked"]]

hour = datetime.now().hour
greet = ("Selamat pagi" if hour < 11
        else "Selamat siang" if hour < 15
        else "Selamat sore" if hour < 19
        else "Selamat malam")


def _goto_lesson(lesson_id):
    flow.start_practice(lesson_id)
    st.switch_page("ui_pages/student_practice.py")

st.markdown(f"## {greet}, {user['name']}! 👋")
st.caption("Selamat datang kembali di dashboard belajarmu.")

c1, c2, c3 = st.columns([2, 1, 1])
with c1:
    ui.stat_card("Progress Belajar", f"{stats['avg_accuracy']:.0f}%",
                delta=f"{stats['total_sessions']} sesi latihan")
with c2:
    ui.stat_card("Level", level["name"])
with c3:
    ui.stat_card("XP", f"{xp:,} XP")

st.markdown(
    ui.xp_bar_html(level["pct"],
                label=f'Level {level["name"]}', height=10),
    unsafe_allow_html=True,
)
if level["next_xp"] is not None:
    st.caption(f'{level["next_xp"] - xp} XP lagi menuju level berikutnya.')
else:
    st.caption("Level maksimum tercapai, hebat! 🎉")

st.divider()
s1, s2, s3, s4 = st.columns(4)
with s1:
    ui.stat_card("Total Latihan", stats["total_sessions"])
with s2:
    ui.stat_card("Akurasi Rata-rata", f"{stats['avg_accuracy']:.0f}%")
with s3:
    ui.stat_card("Materi Dipelajari", stats["materials_done"])
with s4:
    ui.stat_card("Streak", f"{streak['streak']} hari 🔥")

st.divider()
left, right = st.columns([2, 1])

with left:
    ui.section_mini("Lanjutkan Belajar", "Materi terakhir yang kamu pelajari")
    if last:
        st.markdown(
            f'<div class="stat-card">'
            f'<div style="color:{ui.MUTED};font-size:0.85rem;font-weight:600">'
            f'{last["material_title"] or "Materi"} · {last["category"] or ""}</div>'
            f'<div style="font-size:1.35rem;font-weight:800;margin:0.2rem 0">'
            f'{last["target"]}</div>'
            f'<div style="color:{ui.MUTED}">{last["lesson_title"] or ""}</div>'
            f'</div>',
            unsafe_allow_html=True,
        )
        st.button("Lanjutkan Latihan", type="primary",
                on_click=lambda: _goto_lesson(last["lesson_id"]))
    else:
        ui.empty_state("🚀", "Belum ada latihan. Mulai dari materi Alfabet!")
        st.button("Mulai Latihan", type="primary",
                on_click=lambda: st.switch_page("ui_pages/student_practice.py"))

    st.divider()
    ui.section_mini("AI Learning Recommendation", "Rekomendasi berdasarkan datamu")
    st.markdown(
        f'<div class="stat-card">'
        f'<div style="font-weight:800">{recommend["title"]}</div>'
        f'<div style="color:{ui.MUTED};margin-top:0.3rem">{recommend["text"]}</div>'
        f'</div>',
        unsafe_allow_html=True,
    )

with right:
    ui.section_mini("Daily Challenge", "Selesaikan untuk +XP")
    for ch in challenges:
        prog = ch["progress"] or 0
        pct = min(100, int(prog / ch["target"] * 100)) if ch["target"] else 0
        done = " ✅" if ch["completed"] else ""
        st.markdown(
            f'<div class="stat-card" style="margin-bottom:0.6rem">'
            f'<div style="font-weight:700;font-size:0.95rem">{ch["title"]}{done}</div>'
            f'<div style="color:{ui.MUTED};font-size:0.8rem">'
            f'{prog}/{ch["target"]} selesai · +{ch["reward_xp"]} XP</div>'
            + ui.xp_bar_html(pct, height=6) +
            f'</div>',
            unsafe_allow_html=True,
        )
    if not challenges:
        st.info("Tidak ada challenge hari ini.")

    st.divider()
    ui.section_mini("Achievement Terbaru", "Terus asah kemampuanmu")
    if newest_unlocked:
        badge = newest_unlocked[0]
        st.markdown(
            f'<div class="stat-card" style="text-align:center">'
            f'<div style="font-size:2rem">🏆</div>'
            f'<div style="font-weight:800;font-size:1.05rem">{badge["name"]}</div>'
            f'<div style="color:{ui.MUTED};font-size:0.85rem">{badge["description"]}</div>'
            f'</div>',
            unsafe_allow_html=True,
        )
    else:
        st.success("Selesaikan latihan pertama untuk membuka badge.")

st.divider()
st.caption("Tip: latihan setiap hari menjaga streak dan meningkatkan akurasi 🔥")