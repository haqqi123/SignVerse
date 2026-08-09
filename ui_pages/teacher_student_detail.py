"""Detail Siswa (Guru) — monitoring progress, error analysis, riwayat."""

import streamlit as st

from signlib import gamification, service, ui
from signlib.auth import require_role

require_role("teacher")

student_id = st.session_state.get("teacher_selected_student")
if not student_id:
    st.warning("Pilih siswa terlebih dahulu dari halaman Siswa.")
    st.button("Ke halaman Siswa", on_click=lambda: st.switch_page("ui_pages/teacher_students.py"))
    st.stop()

detail = service.student_detail(student_id)
ui.section_header(
    f"Detail Siswa: {detail['name']}",
    f"Akun {detail['username']} · terakhir aktif {detail['last_activity'] or '-'}",
)

c1, c2, c3, c4 = st.columns(4)
with c1:
    ui.stat_card("Total Latihan", detail["stats"]["total_sessions"])
with c2:
    ui.stat_card("Akurasi", f'{detail["stats"]["avg_accuracy"]:.0f}%')
with c3:
    ui.stat_card("XP", f'{gamification.total_xp(student_id):,}')
with c4:
    ui.stat_card("Streak", f'{gamification.streak_info(student_id)["streak"]} hari')

st.divider()

# ── Akurasi per kategori & Error Analysis ──────────────────────────────
left, right = st.columns(2)
with left:
    ui.section_mini("Akurasi per Kategori", "Nilai dari data latihan nyata")
    cat = detail["category_accuracy"]
    if cat:
        for name, data in cat.items():
            st.markdown(
                f'<div style="font-weight:700;font-size:0.9rem;margin-top:0.4rem">'
                f'{name.capitalize()} — {data["accuracy"]:.0f}%</div>'
                + ui.xp_bar_html(data["accuracy"], height=8),
                unsafe_allow_html=True,
            )
    else:
        st.caption("Belum ada data latihan.")

with right:
    ui.section_mini(
        "Analisis Kesalahan (Error Analysis)",
        "Berbasis hasil deteksi nyata pada data latihan siswa",
    )
    errors = detail["errors"]
    if errors:
        for e in errors:
            status = "Good" if e["acc"] >= 85 else "Perlu Latihan"
            emoji = "✅" if e["acc"] >= 85 else "⚠️"
            st.markdown(
                f'<div class="stat-card" style="padding:0.65rem 0.9rem;margin-bottom:0.4rem">'
                f'<div style="display:flex;justify-content:space-between;font-weight:700">'
                f'<span>{emoji} Gesture "{e["expected"]}"</span>'
                f'<span>{e["acc"]:.0f}%</span></div>'
                f'<div style="color:{ui.MUTED};font-size:0.85rem">'
                f'{e["wrong"]} salah dari {e["n"]} percobaan · Status: {status}</div>'
                f'</div>',
                unsafe_allow_html=True,
            )
        st.caption("Hasil dihitung berdasar data gesture log yang nyata.")
    else:
        st.caption("Belum ada data huruf untuk dianalisis.")

st.divider()

# ── Riwayat latihan ────────────────────────────────────────────────────
ui.section_mini("Riwayat Latihan (10 terakhir)", "Sesi terbaru siswa")
history = service.practice_history(student_id, 10)
if history:
    rows = [
        {"Tanggal": h["created_at"][:16], "Materi": h["material_title"] or "-",
         "Latihan": h["lesson_title"] or h["target"],
         "Akurasi": f'{h["accuracy"]:.0f}%',
         "Skor": f'{h["final_score"]:.0f}', "Grade": h["grade"]}
        for h in history
    ]
    st.table(rows)
else:
    st.caption("Belum ada riwayat latihan.")

st.divider()

# ── Achievement siswa ─────────────────────────────────────────────────
ui.section_mini("Achievement Siswa", "Badge yang sudah dikumpulkan")
badges = gamification.achievements(student_id)
unlocked = [b for b in badges if b["unlocked"]]
if unlocked:
    cols = st.columns(len(unlocked) or 1)
    for i, b in enumerate(unlocked):
        with cols[i]:
            st.markdown(
                f'<div class="stat-card" style="text-align:center;padding:0.7rem">'
                f'<div style="font-size:1.5rem">🏆</div>'
                f'<div style="font-weight:700;font-size:0.85rem">{b["name"]}</div>'
                f'</div>',
                unsafe_allow_html=True,
            )
else:
    st.caption("Belum ada badge yang diraih.")

st.divider()

# ── Assignment siswa ─────────────────────────────────────────────────
ui.section_mini("Assignment", "Status tugas siswa")
assignments = service.student_assignments(student_id)
if assignments:
    rows = [
        {"Judul": a["title"], "Materi": a["material_title"] or "-",
         "Status": a["status"], "Deadline": a["deadline"] or "-"}
        for a in assignments
    ]
    st.table(rows)
else:
    st.caption("Belum ada assignment untuk siswa ini.")