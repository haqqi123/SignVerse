"""Assignment (Guru) — buat tugas, pilih materi/siswa, deadline, status."""

import streamlit as st

from signlib import service, ui
from signlib.auth import require_role

require_role("teacher")
user = st.session_state["user"]

ui.section_header("Assignment", "Kelola tugas untuk siswamu")

# ── Buat assignment ────────────────────────────────────────────────────
with st.expander("➕ Buat Assignment Baru", expanded=False):
    materials = service.get_materials()
    mat_map = {m["title"]: m for m in materials}
    mat_choice = st.selectbox("Materi", list(mat_map.keys()))
    new_title = st.text_input("Judul tugas", placeholder="Latihan Alfabet A-E")
    new_desc = st.text_area("Deskripsi", placeholder="Kerjakan latihan huruf A sampai E...")
    students = service.all_students()
    if not students:
        st.warning("Belum ada siswa terdaftar.")
    else:
        student_map = {s["name"]: s["id"] for s in students}
        chosen = st.multiselect("Pilih siswa", list(student_map.keys()))
        deadline = st.date_input("Deadline")
        new_desc = new_desc or ""
        if st.button("Buat Tugas", type="primary"):
            if not new_title:
                st.warning("Judul tugas wajib diisi.")
            elif not chosen:
                st.warning("Pilih minimal satu siswa.")
            else:
                service.create_assignment(
                    user["id"], mat_map[mat_choice]["id"], new_title, new_desc,
                    [student_map[s] for s in chosen], deadline.isoformat(),
                )
                st.toast("Assignment dibuat!")

st.divider()

ui.section_mini("Daftar Assignment", "Status pengerjaan setiap tugas")
assignments = service.teacher_assignments(user["id"])
if not assignments:
    st.info("Belum ada assignment. Buat lewat form di atas.")
    st.stop()

for a in assignments:
    badge_info = f'{a["done"]}/{a["total_students"]} selesai'
    st.markdown(
        f'<div class="stat-card" style="margin-bottom:0.6rem">'
        f'<div style="font-weight:800;font-size:1.05rem">{a["title"]} '
        f'{ui.badge(badge_info, "teal")}</div>'
        f'<div style="color:{ui.MUTED};font-size:0.85rem">Materi: {a["material_title"] or "-"} · '
        f'Deadline: {a["deadline"] or "-"}</div>'
        f'</div>',
        unsafe_allow_html=True,
    )
    with st.expander(f"Detail — {a['title']}"):
        items = service.assignment_students(a["id"])
        rows = [
            {"Siswa": it["name"], "Status": it["status"],
             "Selesai": it["completed_at"][:10] if it["completed_at"] else "-"}
            for it in items
        ]
        st.table(rows)