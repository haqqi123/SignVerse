"""Dashboard Guru — ringkasan kelas."""

import streamlit as st

from signlib import service, ui
from signlib.auth import require_role

require_role("teacher")
user = st.session_state["user"]

ui.section_header(f"Dashboard Guru", f"Selamat datang, {user['name']} 👋")

summary = service.teacher_summary()
c1, c2, c3, c4 = st.columns(4)
with c1:
    ui.stat_card("Siswa", summary["students"])
with c2:
    ui.stat_card("Rata-rata Skor", f'{summary["avg_score"]:.0f}%')
with c3:
    ui.stat_card("Latihan Minggu Ini", summary["weekly_sessions"])
with c4:
    ui.stat_card("Assignment Aktif", summary["active_assignments"])

st.divider()

# ── Aktivitas mingguan ─────────────────────────────────────────────────
weekly = service.weekly_activity(7)
ui.section_mini("Aktivitas Minggu Ini", "Jumlah latihan per hari (7 hari terakhir)")
if weekly:
    import pandas as pd

    df = pd.DataFrame(weekly).rename(columns={"d": "Tanggal", "n": "Latihan"})
    st.bar_chart(df.set_index("Tanggal")["Latihan"], height=220)
else:
    st.info("Belum ada aktivitas latihan minggu ini.")

st.divider()

# ── Assignment terbaru ─────────────────────────────────────────────────
ui.section_mini("Assignment Terbaru", "Tugas yang sudah kamu buat")
assignments = service.teacher_assignments(user["id"])
if assignments:
    rows = [
        {
            "Judul": a["title"],
            "Materi": a["material_title"] or "-",
            "Siswa": a["total_students"],
            "Selesai": f'{a["done"]}/{a["total_students"]}',
            "Deadline": a["deadline"] or "-",
        }
        for a in assignments[:5]
    ]
    st.table(rows)
else:
    st.caption("Belum ada assignment. Buat dari menu Assignment.")

st.divider()
ui.section_mini("Aksi Cepat")
b1, b2, b3 = st.columns(3)
with b1:
    st.button("Lihat Siswa", use_container_width=True,
              on_click=lambda: st.switch_page("ui_pages/teacher_students.py"))
with b2:
    st.button("Buat Assignment", use_container_width=True,
              on_click=lambda: st.switch_page("ui_pages/teacher_assignments.py"))
with b3:
    st.button("Lihat Report", use_container_width=True,
              on_click=lambda: st.switch_page("ui_pages/teacher_reports.py"))