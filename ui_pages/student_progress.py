"""Progress Siswa — riwayat, akurasi, grafik tren."""

import streamlit as st

from signlib import gamification, service, ui
from signlib.auth import require_role

require_role("student")
user = st.session_state["user"]
uid = user["id"]

ui.section_header("Progress Belajar", "Perjalanan belajarmu sejauh ini")

stats = service.student_stats(uid)
streak = gamification.streak_info(uid)
xp = gamification.total_xp(uid)
level = gamification.level_from_xp(xp)

c1, c2, c3, c4 = st.columns(4)
with c1:
    ui.stat_card("Total Latihan", stats["total_sessions"])
with c2:
    ui.stat_card("Akurasi Rata-rata", f"{stats['avg_accuracy']:.0f}%")
with c3:
    ui.stat_card("Level", level["name"],
                 delta=f"{xp:,} XP")
with c4:
    ui.stat_card("Streak", f"{streak['streak']} hari 🔥")

st.divider()

# ── Kategori accuracy ──────────────────────────────────────────────────
cat = service.category_accuracy(uid)
ui.section_mini("Akurasi per Kategori", "Rata-rata akurasi latihan per kategori materi")
if cat:
    for name, data in cat.items():
        st.markdown(
            f'<div style="font-weight:700;font-size:0.9rem;margin-top:0.4rem">'
            f'{name.capitalize()} — {data["accuracy"]:.0f}%</div>'
            + ui.xp_bar_html(data["accuracy"], height=8),
            unsafe_allow_html=True,
        )
else:
    st.info("Belum ada data latihan.")

st.divider()

# ── Accuracy trend (7 hari) ────────────────────────────────────────────
trend = service.accuracy_trend(uid, 7)
ui.section_mini("Accuracy Trend (7 hari terakhir)", "Grafik akurasi harianmu")
if trend:
    import pandas as pd

    df = pd.DataFrame(trend)
    st.line_chart(df.set_index("day")["accuracy"], color="#6366f1", height=240)
else:
    st.info("Belum ada data untuk digrafik.")

st.divider()

# ── Riwayat latihan ────────────────────────────────────────────────────
ui.section_mini("Riwayat Latihan", "Latihan terakhirmu")
history = service.practice_history(uid, 20)
if history:
    rows = [
        {
            "Tanggal": r["created_at"][:16],
            "Materi": r["material_title"] or "-",
            "Latihan": r["lesson_title"] or r["target"],
            "Akurasi": f"{r['accuracy']:.0f}%",
            "Skor": f"{r['final_score']:.0f}",
            "Grade": r["grade"],
        }
        for r in history
    ]
    st.table(rows)
else:
    st.info("Belum ada latihan. Yuk mulai dari halaman Materi!")

st.divider()
st.caption("Latihan rutin setiap hari adalah kunci increase akurasi & streak 🔥")