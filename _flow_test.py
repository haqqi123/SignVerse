"""Verifikasi fungsi alur baru (assignment auto-complete, flow, practice running)."""
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from signlib import db, service  # noqa: E402

db.init_db(reset=True)

# 1) first_lesson
first = service.first_lesson(1)
assert first and first["id"] == 1, "first_lesson gagal"
print("[OK] first_lesson")

# 2) assignment auto-complete
uid = 2  # siswa@signteach.id
aid = service.create_assignment(
    1, 1, "Latihan Alfabet", "Kerjakan alfabet", [uid], "2026-12-31",
)
service.update_assignment_status(uid, aid, "sedang dikerjakan")
done = service.complete_assignments_for_material(uid, 1)
assert done == ["Latihan Alfabet"], f"auto-complete salah: {done}"
item = service.student_assignments(uid)
assert item[0]["status"] == "selesai", "status assignment tidak selesai"
print("[OK] complete_assignments_for_material")

# 3) tidak menyentuh assignment materi lain / sudah selesai
service.update_assignment_status(uid, aid, "sedang dikerjakan")
done2 = service.complete_assignments_for_material(uid, 99)  # materi tak ada
assert done2 == [], "seharusnya tidak ada assignment yang tersentuh"
print("[OK] complete_assignments_for_material (material lain aman)")

# 4) flow.start_practice session state
import streamlit as st  # noqa: E402
from signlib.flow import start_practice  # noqa: E402

st.session_state["practice_lesson_id"] = 5
st.session_state["practice_status"] = "done"
st.session_state["practice_mem"] = object()
start_practice(3)
assert st.session_state["practice_lesson_id"] == 3
assert st.session_state["practice_status"] == "idle"
assert "practice_mem" not in st.session_state
start_practice()  # tanpa id → kembali ke pemilihan
assert "practice_lesson_id" not in st.session_state
print("[OK] flow.start_practice")

print("\nSEMUA FUNGSI BARU OK")
