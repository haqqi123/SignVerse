"""Materi Belajar — Alfabet, Angka, Kosakata (SIBI & BISINDO)."""

import streamlit as st

from signlib import flow, service, ui
from signlib.auth import require_role

require_role("student")

ui.section_header("Materi Belajar", "Pilih materi dan mulai latihan")

materials = service.get_materials()
mat_alfabet = [m for m in materials if m["category"] == "alfabet"]
mat_angka = [m for m in materials if m["category"] == "angka"]
mat_kosakata = [m for m in materials if m["category"] == "kosakata"]

tab_alfabet, tab_angka, tab_kosakata = st.tabs(["🔤 Alfabet", "🔢 Angka", "🗣️ Kosakata Umum"])

CATEGORY_ICON = {"alfabet": "🔤", "angka": "🔢", "kosakata": "🗣️"}


def render_material(material):
    lessons = service.get_lessons(material["id"])
    system_badge = ui.badge(material["sign_system"],
                            "indigo" if material["sign_system"] == "SIBI" else "teal")
    st.markdown(
        f'<div class="stat-card" style="margin-bottom:0.8rem">'
        f'<div style="font-weight:800;font-size:1.1rem">{material["title"]} {system_badge}</div>'
        f'<div style="color:{ui.MUTED}">{material["description"]}</div>'
        f'<div style="color:{ui.MUTED};font-size:0.85rem;margin-top:0.3rem">'
        f'{len(lessons)} materi</div>'
        f'</div>',
        unsafe_allow_html=True,
    )

    with st.expander("Lihat daftar latihan & mulai", expanded=False):
        cols = st.columns(4)
        for i, lesson in enumerate(lessons):
            with cols[i % 4]:
                target = lesson["practice_target"] or lesson["target"]
                st.markdown(
                    f'<div class="stat-card" style="padding:0.7rem;text-align:center;'
                    f'min-height:110px">'
                    f'<div style="font-weight:800;font-size:1.2rem">{target}</div>'
                    f'<div style="color:{ui.MUTED};font-size:0.75rem">{lesson["title"]}</div>'
                    f'<div style="font-size:0.7rem;color:{ui.PRIMARY};font-weight:600;'
                    f'margin-top:0.3rem">{lesson["practice_mode"].upper()}</div>'
                    f'</div>',
                    unsafe_allow_html=True,
                )
                if st.button("Latih", key=f"lesson_{lesson['id']}",
                             use_container_width=True):
                    flow.start_practice(lesson["id"])
                    st.switch_page("ui_pages/student_practice.py")


with tab_alfabet:
    for m in mat_alfabet:
        render_material(m)

with tab_angka:
    for m in mat_angka:
        render_material(m)

with tab_kosakata:
    for m in mat_kosakata:
        render_material(m)

st.divider()
st.caption(
    "Catatan: model deteksi mengenali isyarat huruf (fingerspelling) dari sistem "
    "SIBI. Kata dibangun dengan mengeja huruf, misal SAYA = S-A-Y-A."
)