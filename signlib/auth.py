"""Autentikasi & session SignTeach.

Login sederhana (demo): username + password disimpan dengan hash SHA-256.
Role: 'student' dan 'teacher'. Role guard dilakukan di setiap halaman.
"""

import streamlit as st

from . import db


def login(username: str, password: str):
    row = db.query(
        "SELECT * FROM users WHERE username = ? AND password_hash = ?",
        (username, db.hash_password(password)), one=True,
    )
    if row:
        st.session_state["user"] = dict(row)
        st.session_state["auth_time"] = True
        return row
    return None


def register(name: str, username: str, password: str):
    """Buat akun siswa baru. Return (ok, message)."""
    if not name.strip():
        return False, "Nama wajib diisi."
    if "@" not in username or "." not in username.split("@")[-1]:
        return False, "Masukkan email yang valid."
    if len(password) < 6:
        return False, "Password minimal 6 karakter."
    existing = db.query(
        "SELECT id FROM users WHERE username = ?", (username,), one=True,
    )
    if existing:
        return False, "Email sudah terdaftar. Gunakan email lain atau masuk langsung."
    db.execute(
        "INSERT INTO users (username, password_hash, name, role) VALUES (?,?,?, 'student')",
        (username, db.hash_password(password), name.strip()),
    )
    return True, "Akun berhasil dibuat. Selamat belajar!"


def logout():
    st.session_state.pop("user", None)
    st.session_state.pop("auth_time", None)


def current_user():
    return st.session_state.get("user")


def is_logged_in():
    return "user" in st.session_state


def require_login():
    """Redirect ke halaman login jika belum login."""
    if not is_logged_in():
        st.switch_page("ui_pages/login.py")


def require_role(*roles):
    """Guard: pastikan role sesuai; jika tidak, tampilkan pesan + redirect."""
    user = current_user()
    if not user:
        from streamlit.runtime.scriptrunner import get_script_run_ctx
        ctx = get_script_run_ctx()
        if ctx and ctx.main_script_path:
            st.session_state["redirect_after_login"] = ctx.main_script_path
        st.switch_page("ui_pages/login.py")
    if user["role"] not in roles:
        st.error("Anda tidak memiliki akses ke halaman ini.")
        home = "ui_pages/student_dashboard.py" if user["role"] == "student" \
            else "ui_pages/teacher_dashboard.py"
        st.button("Kembali ke Dashboard", on_click=lambda: st.switch_page(home))
        st.stop()


def redirect_after_login(user):
    """Redirect sesuai flag redirect_after_login (jika sesuai role)."""
    target = st.session_state.pop("redirect_after_login", None)
    if not target:
        return False
    role_ok = (user["role"] == "student" and "student" in target) \
        or (user["role"] == "teacher" and "teacher" in target)
    if role_ok:
        st.switch_page(target)
        return True
    return False


def greeting_name():
    user = current_user()
    return user["name"] if user else "Sobat SignTeach"