"""Smoke test frontend: jalankan tiap halaman lewat AppTest."""
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from signlib import db  # noqa: E402

db.init_db(reset=True)

from streamlit.testing.v1 import AppTest  # noqa: E402

STUDENT = {
    "id": 2, "username": "siswa@signteach.id", "name": "Rina Putri", "role": "student",
}
TEACHER = {
    "id": 1, "username": "guru@signteach.id", "name": "Budi Santoso", "role": "teacher",
}

PAGES = [
    ("ui_pages/landing.py", None, {}),
    ("ui_pages/login.py", None, {}),
    ("ui_pages/register.py", None, {}),
    ("ui_pages/student_dashboard.py", STUDENT, {}),
    ("ui_pages/student_materials.py", STUDENT, {}),
    ("ui_pages/student_practice.py", STUDENT, {}),  # layar pemilihan
    ("ui_pages/student_practice.py", STUDENT, {"practice_lesson_id": 1}),  # layar latihan
    ("ui_pages/student_progress.py", STUDENT, {}),
    ("ui_pages/student_achievements.py", STUDENT, {}),
    ("ui_pages/student_challenge.py", STUDENT, {}),
    ("ui_pages/student_assignment.py", STUDENT, {}),
    ("ui_pages/student_profile.py", STUDENT, {}),
    ("ui_pages/teacher_dashboard.py", TEACHER, {}),
    ("ui_pages/teacher_students.py", TEACHER, {}),
    ("ui_pages/teacher_student_detail.py", TEACHER, {"teacher_selected_student": 2}),
    ("ui_pages/teacher_assignments.py", TEACHER, {}),
    ("ui_pages/teacher_reports.py", TEACHER, {}),
    ("ui_pages/teacher_profile.py", TEACHER, {}),
]

failures = []
for page, user, extra_state in PAGES:
    at = AppTest.from_file(page, default_timeout=30)
    if user:
        at.session_state["user"] = user
    for k, v in extra_state.items():
        at.session_state[k] = v
    try:
        at.run()
        if at.exception:
            failures.append((page, "EXCEPTION: " + str(at.exception)))
        else:
            print(f"[OK] {page}")
    except Exception as e:  # noqa: BLE001
        failures.append((page, f"RUN ERROR: {type(e).__name__}: {e}"))

print("\n===== SUMMARY =====")
if failures:
    for page, err in failures:
        print(f"[FAIL] {page}\n   {err}")
    sys.exit(1)
else:
    print("ALL PAGES OK")