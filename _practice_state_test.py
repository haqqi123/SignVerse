"""Test practice page dalam kondisi running (tanpa kamera/AppTest)."""
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from signlib import db  # noqa: E402

db.init_db(reset=True)

from streamlit.testing.v1 import AppTest  # noqa: E402

STUDENT = {
    "id": 2, "username": "siswa@signteach.id", "name": "Rina Putri", "role": "student",
}

cases = [
    ("practice running (kamera off)", {"practice_lesson_id": 1, "practice_status": "running"}),
    ("practice idle + lesson dipilih", {"practice_lesson_id": 1, "practice_status": "idle"}),
    ("assignment + practice state", {"practice_lesson_id": 1, "practice_status": "idle",
                                    "last_assignment_completed": ["Latihan Alfabet"]}),
]

from signlib.practice_state import SessionMemory  # noqa: E402

mem_case = {
    "practice_lesson_id": 1, "practice_status": "running",
    "practice_mem": SessionMemory("SAYA", "SAYA"),
}
cases.append(("practice running + mem valid", mem_case))
for label, extra in cases:
    at = AppTest.from_file("ui_pages/student_practice.py", default_timeout=60)
    at.session_state["user"] = STUDENT
    for k, v in extra.items():
        at.session_state[k] = v
    at.run()
    if at.exception:
        print(f"[FAIL] {label}: {at.exception}")
        sys.exit(1)
    print(f"[OK] {label}")

print("PRACTICE STATES OK")
