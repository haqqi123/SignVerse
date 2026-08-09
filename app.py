"""SignTeach - SignLanguage Learning Platform (Streamlit).

Entry point aplikasi: routing role-based dengan st.navigation.
Landing page ditampilkan tanpa login; setelah login navigasi
berubah sesuai peran (siswa/guru).

Model deteksi tetap memakai YOLO existing (best.pt).
"""

import streamlit as st

from signlib import db, ui
from signlib.auth import current_user, is_logged_in

st.set_page_config(
    page_title="SignTeach - Belajar Bahasa Isyarat",
    page_icon="🤟",
    layout="wide",
    initial_sidebar_state="expanded",
)

db.init_db()
ui.inject_css()


def build_navigation():
    if not is_logged_in():
        return [
            st.Page("ui_pages/landing.py", title="Beranda", icon="🏠", default=True),
            st.Page("ui_pages/login.py", title="Masuk", icon="🔑"),
            st.Page("ui_pages/register.py", title="Daftar", icon="📝"),
        ]

    user = current_user()
    if user["role"] == "student":
        return [
            st.Page("ui_pages/student_dashboard.py", title="Dashboard", icon="🏠", default=True),
            st.Page("ui_pages/student_materials.py", title="Materi Belajar", icon="📚"),
            st.Page("ui_pages/student_practice.py", title="AI Practice", icon="✋"),
            st.Page("ui_pages/student_challenge.py", title="Challenge Harian", icon="🎯"),
            st.Page("ui_pages/student_progress.py", title="Progress", icon="📈"),
            st.Page("ui_pages/student_achievements.py", title="Achievement", icon="🏆"),
            st.Page("ui_pages/student_assignment.py", title="Assignment", icon="📝"),
            st.Page("ui_pages/inclusive.py", title="Inclusive Communication", icon="💬"),
            st.Page("ui_pages/student_profile.py", title="Profil", icon="👤"),
        ]
    return [
        st.Page("ui_pages/teacher_dashboard.py", title="Dashboard", icon="🏠", default=True),
        st.Page("ui_pages/teacher_students.py", title="Siswa", icon="👥"),
        st.Page("ui_pages/teacher_student_detail.py", title="Monitoring", icon="🧑‍🎓"),
        st.Page("ui_pages/teacher_assignments.py", title="Assignment", icon="📋"),
        st.Page("ui_pages/teacher_reports.py", title="Laporan & Report", icon="📊"),
        st.Page("ui_pages/teacher_profile.py", title="Profil", icon="👤"),
    ]


pg = st.navigation(build_navigation(), position="sidebar")
pg.run()