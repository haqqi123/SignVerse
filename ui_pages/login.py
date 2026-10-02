"""Halaman Login SignTeach (demo accounts)."""

import streamlit as st

from signlib import ui
from signlib.auth import current_user, login, redirect_after_login

if current_user():
    st.switch_page("ui_pages/student_dashboard.py" if current_user()["role"] == "student"
                else "ui_pages/teacher_dashboard.py")

ui.section_header("Masuk ke SignTeach", "Gunakan akun kamu untuk melanjutkan belajar")

with st.form("login_form"):
    username = st.text_input("Email", placeholder="nama@example.com")
    password = st.text_input("Password", type="password", placeholder="••••••••")
    submitted = st.form_submit_button("Masuk", type="primary", use_container_width=True)

if submitted:
    if not username or not password:
        st.warning("Isi email dan password terlebih dahulu.")
    else:
        user = login(username, password)
        if user:
            redirect_after_login(user) or st.rerun()
        else:
            st.error("Email atau password salah. Coba lagi.")

st.divider()

# ── Quick login demo ───────────────────────────────────────────────────
st.markdown(
    f'<div class="st-card">'
    f'<div style="font-weight:700;margin-bottom:0.4rem">Akun demo</div>'
    f'<div style="color:{ui.MUTED};font-size:0.9rem">'
    f'<b>Siswa</b>: siswa@signteach.id / siswa123 &nbsp;·&nbsp; '
    f'<b>Guru</b>: guru@signteach.id / guru123</div>'
    f'</div>',
    unsafe_allow_html=True,
)
d1, d2 = st.columns(2)
with d1:
    if st.button("⚡ Masuk demo siswa", use_container_width=True, type="primary"):
        login("siswa@signteach.id", "siswa123")
        redirect_after_login(st.session_state["user"]) or st.rerun()
with d2:
    if st.button("⚡ Masuk demo guru", use_container_width=True):
        login("guru@signteach.id", "guru123")
        redirect_after_login(st.session_state["user"]) or st.rerun()

st.divider()
if st.button("Belum punya akun? Daftar di sini 📝", use_container_width=True):
    st.switch_page("ui_pages/register.py")
