"""Monitoring Siswa (Guru) — daftar siswa, status, akses detail."""

import streamlit as st

from signlib import service, ui
from signlib.auth import require_role

require_role("teacher")

ui.section_header("Siswa", "Klik salah satu siswa untuk detail lengkap")

report = service.class_report()
if not report:
    st.info("Belum ada data siswa.")
    st.stop()

rows = []
for s in report:
    last = s["last_activity"] or "-"
    rows.append({
        "Nama": s["name"],
        "Progress": f'{s["accuracy"]:.0f}%',
        "Avg Score": f'{s["score"]:.0f}',
        "Sesi": s["sessions"],
        "Terakhir Aktif": last,
    })
st.table(rows)

st.divider()
ui.section_mini("Pilih siswa untuk lihat detail", "Monitoring & analisis per siswa")

students_choices = {s["name"]: s["id"] for s in report}
choice = st.selectbox("Siswa", list(students_choices.keys()))
if st.button("Buka Detail Siswa", type="primary"):
    st.session_state["teacher_selected_student"] = students_choices[choice]
    st.switch_page("ui_pages/teacher_student_detail.py")