"""Report (Guru) — class report, individual report, export CSV."""

import streamlit as st

from signlib import db, service, ui
from signlib.auth import require_role

require_role("teacher")

ui.section_header("Laporan", "Report perkembangan siswa dan kelas")

tab_class, tab_individual = st.tabs(["📦 Class Report", "🧑‍🎓 Individual Report"])

report = service.class_report()

with tab_class:
    if report:
        rows = [
            {"Nama": r["name"], "Akurasi": f'{r["accuracy"]:.0f}%',
             "Avg Score": f'{r["score"]:.0f}', "Sesi": r["sessions"],
             "Materi": r["materials"], "Terakhir Aktif": r["last_activity"]}
            for r in report
        ]
        st.table(rows)
    else:
        st.info("Belum ada data untuk report kelas.")

    st.divider()
    if report:
        csv_data = "Nama,Akurasi (%),Avg Score,Sesi,Materi,Last Activity\n"
        for r in report:
            csv_data += f'{r["name"]},{r["accuracy"]:.1f},{r["score"]:.1f},{r["sessions"]},{r["materials"]},{r["last_activity"]}\n'
        st.download_button(
            "⬇️ Export Class Report (CSV)", data=csv_data.encode("utf-8-sig"),
            file_name="class_report_signteach.csv", mime="text/csv", type="primary",
        )

with tab_individual:
    if not report:
        st.info("Belum ada data siswa.")
    else:
        name_map = {r["name"]: r["id"] for r in report}
        choice = st.selectbox("Pilih siswa", list(name_map.keys()))
        sid = name_map[choice]
        detail = service.student_detail(sid)

        c1, c2, c3 = st.columns(3)
        with c1:
            ui.stat_card("Akurasi", f'{detail["stats"]["avg_accuracy"]:.0f}%')
        with c2:
            avg_score = db.query(
                "SELECT AVG(final_score) a FROM practice_sessions WHERE user_id = ?",
                (sid,), one=True)["a"] or 0
            ui.stat_card("Skor Rata-rata", f"{avg_score:.0f}")
        with c3:
            ui.stat_card("Total Sesi", detail["stats"]["total_sessions"])

        st.divider()
        cat = detail["category_accuracy"]
        if cat:
            ui.section_mini("Progress per Kategori", "Rata-rata akurasi tiap kategori")
            for name, data in cat.items():
                st.markdown(
                    f'<div style="font-weight:700;font-size:0.9rem;margin-top:0.4rem">'
                    f'{name.capitalize()} — {data["accuracy"]:.0f}%</div>'
                    + ui.xp_bar_html(data["accuracy"], height=8),
                    unsafe_allow_html=True,
                )
        else:
            st.caption("Belum ada data latihan.")

        st.divider()
        history = detail["history"]
        if history:
            rows = [
                {"Tanggal": h["created_at"][:16], "Materi": h["material_title"] or "-",
                 "Latihan": h["lesson_title"] or h["target"],
                 "Akurasi": f'{h["accuracy"]:.0f}%', "Skor": f'{h["final_score"]:.0f}',
                 "Grade": h["grade"]}
                for h in history
            ]
            st.table(rows)
            csv_rows = "Tanggal,Materi,Latihan,Akurasi (%),Skor,Grade\n"
            for h in history:
                csv_rows += f'{h["created_at"][:16]},{h["material_title"] or "-"},{h["lesson_title"] or h["target"]},{h["accuracy"]:.1f},{h["final_score"]:.1f},{h["grade"]}\n'
            st.download_button(
                "⬇️ Export Individual Report (CSV)", data=csv_rows.encode("utf-8-sig"),
                file_name=f"report_{choice}.csv", mime="text/csv", type="primary",
            )
        else:
            st.caption("Belum ada riwayat latihan siswa ini.")