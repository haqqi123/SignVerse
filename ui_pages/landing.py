"""Landing page SignTeach (tanpa login)."""

import streamlit as st

from signlib import service, ui
from signlib.auth import current_user

if current_user():
    target = ("ui_pages/student_dashboard.py" if current_user()["role"] == "student"
              else "ui_pages/teacher_dashboard.py")
    st.switch_page(target)

# CTA 'Coba AI Practice' hanya berlaku untuk kunjungan saat ini;
# bersihkan flag lama agar login berikutnya tidak ter-redirect diam-diam.
st.session_state.pop("redirect_after_login", None)

# ── Hero ───────────────────────────────────────────────────────────────
st.markdown(
    f"""
    <div class="hero">
        <div style="font-size:0.8rem;letter-spacing:0.35em;color:{ui.MUTED};font-weight:700">
            SIGNTEACH &middot; BISINDO &middot; AI-POWERED
        </div>
        <h1><span class="grad">SignTeach</span></h1>
        <div class="sub">Platform AI untuk Belajar Bahasa Isyarat Indonesia</div>
        <div style="margin-top:1.2rem">
            <span class="st-badge badge-indigo">SIBI</span>
            <span class="st-badge badge-teal">BISINDO</span>
            <span class="st-badge badge-amber">AI-Powered</span>
        </div>
    </div>
    """,
    unsafe_allow_html=True,
)

def _goto_practice_after_login():
    """CTA 'Coba AI Practice': setelah login siswa, langsung ke AI Practice."""
    st.session_state["redirect_after_login"] = "ui_pages/student_practice.py"
    st.switch_page("ui_pages/login.py")


c1, c2, c3 = st.columns([1, 1, 1], gap="medium")
with c1:
    st.button("Mulai Belajar", type="primary", use_container_width=True,
              on_click=lambda: st.switch_page("ui_pages/login.py"))
with c2:
    st.button("Coba AI Practice", use_container_width=True,
              on_click=_goto_practice_after_login)
with c3:
    st.button("Masuk sebagai Guru", use_container_width=True,
              on_click=lambda: st.switch_page("ui_pages/login.py"))

st.divider()

# ── Statistik ──────────────────────────────────────────────────────────
ui.section_header("Statistik Platform", "Perkembangan data latihan")
students = service.all_students()
summary = service.teacher_summary()

col1, col2, col3 = st.columns(3)
with col1:
    ui.stat_card("Siswa Terdaftar", len(students))
with col2:
    ui.stat_card("Latihan Minggu Ini", summary["weekly_sessions"])
with col3:
    ui.stat_card("Materi Pembelajaran", len(service.get_materials()))

st.divider()

# ── Fitur ──────────────────────────────────────────────────────────────
ui.section_header("Fitur Utama", "Semua yang kamu butuhkan untuk fasih berbahasa isyarat")
feat = [
    ("✋", "AI Practice", "Latihan isyarat dengan kamera, deteksi real-time, skor dan umpan balik."),
    ("🏆", "Gamification", "XP, level, badge, dan streak untuk menjaga semangat belajar."),
    ("👨‍🏫", "Lengkap untuk Guru", "Dashboard, monitoring, assignment, dan laporan analisis."),
]
cols = st.columns(3)
for i, (icon, title, desc) in enumerate(feat):
    with cols[i]:
        st.markdown(
            f'<div class="st-card" style="text-align:center;padding:1.6rem">'
            f'<div style="font-size:2rem">{icon}</div>'
            f'<div style="font-weight:800;font-size:1.05rem;margin:0.4rem 0">{title}</div>'
            f'<div style="color:{ui.MUTED};font-size:0.9rem">{desc}</div></div>',
            unsafe_allow_html=True,
        )

st.divider()

# ── Cara Kerja ─────────────────────────────────────────────────────────
ui.section_header("Cara Kerja Sistem", "Tiga langkah mudah")
for i, (title, desc) in enumerate([
        ("Pilih Materi", "Alfabet, angka, atau kosakata dasar — di SIBI maupun BISINDO."),
        ("Latihan dengan AI", "Kamera menangkap tanganmu dan AI mendeteksi isyarat secara real-time."),
        ("Terima Nilai & Rekomendasi", "Akurasi, speed, consistency, dan rekomendasi materi berikutnya."),
], start=1):
    col1, col2 = st.columns([1, 5])
    with col1:
        st.markdown(
            f'<div class="stat-card" style="text-align:center;font-weight:800;color:{ui.PRIMARY}">{i}</div>',
            unsafe_allow_html=True,
        )
    with col2:
        st.markdown(
            f'<div class="stat-card"><div style="font-weight:700">{title}</div>'
            f'<div style="color:{ui.MUTED}">{desc}</div></div>',
            unsafe_allow_html=True,
        )

st.divider()

ui.section_header("Kata Mereka", "Pengguna SignTeach")
t1, t2 = st.columns(2)
with t1:
    st.markdown(
        f'<div class="st-card">"Siswa jauh lebih bersemangat karena umpan balik AI langsung."'
        f'<div style="font-weight:700;margin-top:0.6rem">— Budi Santoso, Guru SDLBN</div></div>',
        unsafe_allow_html=True,
    )
with t2:
    st.markdown(
        f'<div class="st-card">"Belajar isyarat jadi tidak bosan, akurasi saya naik setiap minggu."'
        f'<div style="font-weight:700;margin-top:0.6rem">— Rina Putri, Siswa</div></div>',
        unsafe_allow_html=True,
    )

st.divider()

# ── CTA & Footer ───────────────────────────────────────────────────────
st.markdown(
    f'<div class="st-card" style="text-align:center;padding:2.4rem 1.5rem">'
    f'<div style="font-size:1.5rem;font-weight:800">Siap memulai perjalanan isyaratmu?</div>'
    f'<div style="color:{ui.MUTED};margin:0.5rem 0 1.2rem">Daftar dan mulai belajar hari ini.</div>'
    f'</div>',
    unsafe_allow_html=True,
)
btn_c1, btn_c2, btn_c3 = st.columns([1, 1, 1])
with btn_c2:
    st.button("Mulai Belajar Sekarang", type="primary", use_container_width=True,
              on_click=lambda: st.switch_page("ui_pages/login.py"))

st.markdown(
    f'<div style="text-align:center;color:{ui.MUTED};font-size:0.8rem;margin-top:1.6rem">'
    f'SignTeach © 2026 — Platform AI Pembelajaran Bahasa Isyarat Indonesia · SIBI · BISINDO</div>',
    unsafe_allow_html=True,
)