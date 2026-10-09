"""Helper alur navigasi latihan (session state).

Memusatkan logika "pindah ke halaman practice" agar konsisten dari
dashboard, materi, assignment, dan challenge — termasuk reset state
latihan lama agar hasil lama tidak muncul lagi.
"""

import streamlit as st


def start_practice(lesson_id=None):
    """Set state practice ke lesson tertentu (reset hasil lama).

    lesson_id=None → kembali ke layar pemilihan latihan.
    """
    if lesson_id is not None:
        st.session_state["practice_lesson_id"] = int(lesson_id)
    else:
        st.session_state.pop("practice_lesson_id", None)
    st.session_state["practice_status"] = "idle"
    st.session_state.pop("practice_mem", None)
    st.session_state.pop("last_result", None)
    st.session_state.pop("last_assignment_completed", None)
