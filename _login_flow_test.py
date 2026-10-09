"""Test alur login redirect (require_role -> login -> kembali ke halaman)."""
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from signlib import db  # noqa: E402

db.init_db(reset=True)

import streamlit as st  # noqa: E402
from signlib.auth import redirect_after_login  # noqa: E402

# 1) redirect_after_login: siswa + halaman siswa → switch page, flag ter-pop
st.session_state["redirect_after_login"] = "ui_pages/student_progress.py"
calls = []
orig_switch = st.switch_page
st.switch_page = lambda page: calls.append(page)
try:
    assert redirect_after_login({"role": "student"}) is True
    assert calls == ["ui_pages/student_progress.py"], calls
    assert "redirect_after_login" not in st.session_state
finally:
    st.switch_page = orig_switch
print("[OK] redirect siswa -> halaman siswa")

# 2) Teacher + halaman siswa → ditolak (flag di-pop, tidak redirect)
st.session_state["redirect_after_login"] = "ui_pages/student_progress.py"
calls = []
orig_switch = st.switch_page
st.switch_page = lambda page: calls.append(page)
try:
    assert redirect_after_login({"role": "teacher"}) is False
    assert calls == [], calls
finally:
    st.switch_page = orig_switch
print("[OK] redirect teacher -> halaman siswa ditolak")

# 3) Siswa + halaman guru → ditolak
st.session_state["redirect_after_login"] = "ui_pages/teacher_reports.py"
calls = []
orig_switch = st.switch_page
st.switch_page = lambda page: calls.append(page)
try:
    assert redirect_after_login({"role": "student"}) is False
    assert calls == [], calls
finally:
    st.switch_page = orig_switch
print("[OK] redirect siswa -> halaman guru ditolak")

# 4) Quick-login demo di halaman login (AppTest)
# Catatan: AppTest menjalankan login.py sebagai main script sehingga
# switch_page("ui_pages/...") tidak resolve — itu keterbatasan harness,
# bukan bug aplikasi. Yang diverifikasi: login() tereksekusi (user ter-set).
from streamlit.testing.v1 import AppTest  # noqa: E402

at = AppTest.from_file("ui_pages/login.py", default_timeout=60)
at.run()
assert not at.exception, f"login page: {at.exception}"
demo_btn = [b for b in at.button if "demo siswa" in (b.label or "")]
assert demo_btn, "tombol quick-login siswa tidak ada"
demo_btn[0].click().run()
assert "user" in at.session_state, "quick-login siswa tidak login"
assert at.session_state["user"]["role"] == "student"
print("[OK] quick-login demo siswa (login berhasil)")

at = AppTest.from_file("ui_pages/login.py", default_timeout=60)
at.run()
demo_btn = [b for b in at.button if "demo guru" in (b.label or "")]
assert demo_btn, "tombol quick-login guru tidak ada"
demo_btn[0].click().run()
assert "user" in at.session_state, "quick-login guru tidak login"
assert at.session_state["user"]["role"] == "teacher"
print("[OK] quick-login demo guru (login berhasil)")

print("\nLOGIN FLOW OK")
