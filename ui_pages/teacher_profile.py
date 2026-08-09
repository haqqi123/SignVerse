"""Profil Guru — info akun + logout."""

import streamlit as st

from signlib import ui
from signlib.auth import logout, require_role

require_role("teacher")
user = st.session_state["user"]

ui.section_header("Profil Guru", "Informasi akun Anda")

c1, c2 = st.columns([1, 2])
with c1:
    st.markdown(
        f'<div class="stat-card" style="text-align:center;padding:1.6rem">'
        f'<div style="font-size:3rem">🧑‍🏫</div>'
        f'<div style="font-weight:800;font-size:1.2rem">{user["name"]}</div>'
        f'<div style="color:{ui.MUTED}">{user["username"]}</div>'
        f'<div style="margin-top:0.6rem">{ui.badge("Guru", "teal")}</div>'
        f'</div>',
        unsafe_allow_html=True,
    )
    if st.button("Keluar (Logout)", type="secondary", use_container_width=True):
        logout()
        st.rerun()

with c2:
    st.markdown(
        f'<div class="stat-card">'
        f'<div style="font-weight:800">Tentang role Guru</div>'
        f'<div style="color:{ui.MUTED};margin-top:0.3rem">Sebagai guru kamu dapat '
        f'memantau perkembangan siswa, membuat assignment, dan melihat laporan '
        f'kemajuan kelas melalui menu Siswa, Assignment, dan Laporan.</div>'
        f'</div>',
        unsafe_allow_html=True,
    )
    st.markdown(
        f'<div class="stat-card">'
        f'<div style="font-weight:800">Panduan singkat</div>'
        f'<div style="color:{ui.MUTED};margin-top:0.3rem">1. Buka menu <b>Siswa</b> untuk '
        f'melihat status tiap siswa.<br>2. Buat <b>Assignment</b> agar siswa dapat '
        f'mengerjakan materi.<br>3. Lihat <b>Laporan</b> untuk analisis kelas.</div>'
        f'</div>',
        unsafe_allow_html=True,
    )