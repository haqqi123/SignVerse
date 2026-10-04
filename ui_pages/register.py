"""Registrasi akun siswa baru."""

import streamlit as st

from signlib import ui
from signlib.auth import current_user, login, redirect_after_login, register

if current_user():
    if redirect_after_login(current_user()):
        st.stop()
    st.switch_page("ui_pages/student_dashboard.py" if current_user()["role"] == "student"
                else "ui_pages/teacher_dashboard.py")

ui.section_header("Daftar Akun Baru", "Mulai belajar Bahasa Isyarat Indonesia hari ini")

with st.form("register_form"):
    name = st.text_input("Nama lengkap", placeholder="Nama Kamu")
    username = st.text_input("Email", placeholder="nama@example.com")
    password = st.text_input("Password", type="password", placeholder="Minimal 6 karakter")
    confirm = st.text_input("Konfirmasi password", type="password", placeholder="Ulangi password")
    submitted = st.form_submit_button("Daftar Sekarang", type="primary", use_container_width=True)

if submitted:
    errors = []
    if not name.strip():
        errors.append("Nama wajib diisi.")
    if not username.strip() or "@" not in username or "." not in username.split("@")[-1]:
        errors.append("Masukkan email yang valid.")
    if len(password) < 6:
        errors.append("Password minimal 6 karakter.")
    if password != confirm:
        errors.append("Konfirmasi password tidak cocok.")
    if errors:
        for e in errors:
            st.warning(e)
    else:
        ok, msg = register(name.strip(), username.strip().lower(), password)
        if ok:
            st.toast("🎉 Akun berhasil dibuat! Selamat belajar.")
            login(username.strip().lower(), password)
            redirect_after_login(st.session_state["user"]) or st.rerun()
        else:
            st.error(msg)

st.divider()
st.markdown(
    f'<div class="st-card" style="text-align:center">'
    f'<div style="color:{ui.MUTED}">Sudah punya akun?</div></div>',
    unsafe_allow_html=True,
)
b1, b2, b3 = st.columns([1, 1, 1])
with b2:
    st.button("Masuk ke Akun", use_container_width=True,
            on_click=lambda: st.switch_page("ui_pages/login.py"))
