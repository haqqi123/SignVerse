"""Inclusive Communication Room — gesture → text → speech.

Ekstensi dari AI Practice: memakai recognition engine yang sama (YOLO),
tanpa membuat sistem deteksi baru.
"""

import streamlit as st

from signlib.auth import require_role
from signlib.freemode import render_free_mode

require_role("student")

render_free_mode("inclusive", mode_title="Inclusive Communication")