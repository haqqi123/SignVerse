"""Assignment untuk siswa — lihat tugas dari guru, kerjakan, submit.

Alur: tombol "Kerjakan" menandai tugas 'sedang dikerjakan' dan langsung
masuk ke latihan materi tugas. Selesai latihan → tugas otomatis 'selesai'
(ditangani di student_practice saat sesi disimpan).
"""

import streamlit as st

from signlib import flow, service, ui
from signlib.auth import require_role

require_role("student")
user = st.session_state["user"]
uid = user["id"]

ui.section_header("Assignment", "Tugas dari gurumu")

assignments = service.student_assignments(uid)


def _submit(assignment_id, status):
    service.update_assignment_status(uid, assignment_id, status)
    st.toast(f"Status tugas diperbarui: {status}")


def _work_on(a):
    """Tandai dikerjakan (jika belum) dan loncat ke latihan materi tugas."""
    if a["status"] == "belum dimulai":
        service.update_assignment_status(uid, a["id"], "sedang dikerjakan")
    lesson = service.first_lesson(a["material_id"]) if a["material_id"] else None
    flow.start_practice(lesson["id"] if lesson else None)
    st.switch_page("ui_pages/student_practice.py")


if not assignments:
    st.info("Belum ada tugas dari guru.")
    st.stop()

for a in assignments:
    late = ""
    if a["deadline"] and a["status"] != "selesai" and a["deadline"] < st.date.today().isoformat():
        late = f' <span style="color:#dc2626;font-weight:700">(melewati deadline)</span>'
    st.markdown(
        f'<div class="stat-card" style="margin-bottom:0.8rem">'
        f'<div style="font-weight:800;font-size:1.05rem">{a["title"]} {ui.status_pill(a["status"])}</div>'
        f'<div style="color:{ui.MUTED};font-size:0.85rem">{a["description"]}</div>'
        f'<div style="color:{ui.MUTED};font-size:0.85rem;margin-top:0.3rem">'
        f'Materi: {a["material_title"] or "-"} · Deadline: {a["deadline"] or "-"}{late}</div>'
        f'</div>',
        unsafe_allow_html=True,
    )
    if a["status"] == "selesai":
        st.caption("✅ Tugas selesai. Kerjakan latihan lain untuk terus berlatih.")
    else:
        c1, c2 = st.columns(2)
        with c1:
            if a["status"] == "belum dimulai":
                st.button("▶ Kerjakan", type="primary", key=f"work_{a['id']}",
                          on_click=lambda aid=a: _work_on(aid))
            else:
                st.button("▶ Lanjutkan Latihan", type="primary", key=f"work_{a['id']}",
                          on_click=lambda aid=a: _work_on(aid))
        with c2:
            if a["status"] == "sedang dikerjakan":
                st.button("✔ Submit Tugas", key=f"submit_{a['id']}",
                          on_click=lambda aid=a["id"]: _submit(aid, "selesai"))

st.caption("Kerjakan latihan materi tugas sampai selesai; tugas akan otomatis "
           "berstatus selesai. Kamu juga bisa submit manual dari halaman ini.")
