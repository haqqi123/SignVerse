"""AI Practice Room — latihan isyarat kamera real-time + Smart Assessment.

Alur: kamera → deteksi YOLO (model existing, finger spelling A-Z) → huruf
stabil terbaca → tersimpan otomatis ke kata (ejaan) → assessment & feedback.

Catatan kejujuran: recognition engine = YOLO existing (bukan MediaPipe/LSTM);
kata dibangun dengan ejaan huruf.
"""

import time

import av
import streamlit as st
from streamlit_webrtc import RTCConfiguration, WebRtcMode, webrtc_streamer

from signlib import ai, db, flow, gamification, recommendation, scoring, service, ui
from signlib.auth import require_role
from signlib.gamification import evaluate_badges
from signlib.practice_state import SessionMemory, state

require_role("student")
user = st.session_state["user"]
uid = user["id"]

ui.section_header("AI Practice", "Latihan isyarat dengan deteksi AI real-time")

practice_status = st.session_state.get("practice_status", "idle")

# ── Ringkasan (data nyata dari database) ───────────────────────────────
# Statistik di-skip saat latihan berjalan agar siklus rerun tidak
# memboroskan query DB; nilai terakhir di-cache di session.
if practice_status != "running":
    _xp = gamification.total_xp(uid)
    st.session_state["prac_summary"] = {
        "stats": service.student_stats(uid),
        "xp": _xp,
        "level": gamification.level_from_xp(_xp),
        "streak": gamification.streak_info(uid),
    }
summary = st.session_state.get("prac_summary", {}) or {}
stats = summary.get("stats") or {}
level = summary.get("level") or {"name": "-"}
streak = summary.get("streak") or {"streak": 0}

sc1, sc2, sc3, sc4 = st.columns(4)
with sc1:
    ui.stat_card("Total Latihan", stats.get("total_sessions", 0))
with sc2:
    ui.stat_card("Akurasi Rata-rata", f'{stats.get("avg_accuracy", 0):.0f}%')
with sc3:
    ui.stat_card("Level", level["name"])
with sc4:
    ui.stat_card("Streak", f'{streak["streak"]} hari 🔥')
st.divider()

RTC_CONFIG = RTCConfiguration({
    "iceServers": [
        {"urls": ["stun:stun.l.google.com:19302"]},
        {"urls": ["stun:stun1.l.google.com:19302"]},
    ]
})

# Settings deteksi (diupdate tiap rerun; dibaca live oleh callback thread)
SETTINGS = {"conf": 0.30}

# Ambang & jeda untuk mencatat gestur salah (error detection)
WRONG_CONF_MIN = 0.6
WRONG_COOLDOWN_S = 1.2


def current_settings():
    return SETTINGS


with st.sidebar:
    st.subheader("Pengaturan Deteksi")
    conf_threshold = st.slider("Confidence minimum", 0.10, 0.60, 0.30, 0.05)
    auto_capture_min = st.slider("Auto-capture bila confidence ≥", 0.40, 0.95, 0.75, 0.05)
    SETTINGS["conf"] = conf_threshold

# ── Helper & callback (didefinisikan sebelum dipakai tombol) ───────────


def _choose_lesson(lesson_id_):
    flow.start_practice(lesson_id_)


def _reset_lesson():
    st.session_state.pop("practice_lesson_id", None)
    flow.start_practice()


def start_session():
    mem = SessionMemory(letters, target)
    st.session_state["practice_mem"] = mem
    st.session_state["practice_status"] = "running"
    st.session_state.pop("last_result", None)
    st.session_state.pop("last_assignment_completed", None)
    state.start()


def finish_session():
    mem = st.session_state["practice_mem"]
    assessment = scoring.compute_assessment(letters, mem.captures,
                                            mem.confidences, mem.durations,
                                            wrong_count=len(mem.wrong_attempts))
    assessment["wrong_count"] = len(mem.wrong_attempts)
    assessment["recommendation"] = scoring.recommendation(assessment)
    _save_session(mem, assessment)
    state.stop()
    st.session_state["practice_status"] = "done"
    st.session_state["last_result"] = {
        "assessment": assessment,
        "letters": list(letters),
        "captures": list(mem.captures),
    }
    st.rerun()


def _save_session(mem_, assessment):
    sid = db.execute(
        "INSERT INTO practice_sessions "
        "(user_id, material_id, lesson_id, target, practice_mode, accuracy, speed, "
        "consistency, completion, final_score, grade, xp_earned, duration_s) "
        "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
        (uid, lesson["material_id"], lesson["id"], target, mode,
         assessment["accuracy"], assessment["speed"], assessment["consistency"],
         assessment["completion"], assessment["final"], assessment["grade"],
         10, round(mem_.duration_s(), 1)),
    )
    for i in range(len(mem_.captures)):
        expected = letters[i] if i < len(letters) else ""
        predicted = mem_.captures[i]
        feedback = scoring.feedback_for(expected, predicted, mem_.confidences[i])
        db.execute(
            "INSERT INTO gesture_results "
            "(session_id, seq, expected, predicted, confidence, is_correct, feedback, duration_ms) "
            "VALUES (?,?,?,?,?,?,?,?)",
            (sid, i, expected, predicted, mem_.confidences[i],
             int(predicted == expected), feedback,
             int(mem_.durations[i] * 1000)),
        )
    for wi, w in enumerate(mem_.wrong_attempts):
        db.execute(
            "INSERT INTO gesture_results "
            "(session_id, seq, expected, predicted, confidence, is_correct, feedback, duration_ms) "
            "VALUES (?,?,?,?,?,?,?,?)",
            (sid, len(mem_.captures) + wi, w["expected"], w["predicted"],
             w["confidence"], 0,
             scoring.feedback_for(w["expected"], w["predicted"], w["confidence"]), 0),
        )
    service.bump_challenge(uid, service.get_material(lesson["material_id"])["category"])
    evaluate_badges(uid)
    # Assignment: selesai latihan materi tugas → tugas otomatis tuntas
    completed = service.complete_assignments_for_material(uid, lesson["material_id"])
    if completed:
        st.session_state["last_assignment_completed"] = completed
    return sid


def process_frame(frame: av.VideoFrame) -> av.VideoFrame:
    img = frame.to_ndarray(format="bgr24")
    dets = ai.detect_frame(img, conf=current_settings()["conf"])
    if dets:
        best = max(dets, key=lambda d: d["confidence"])
        state.update(best["letter"], best["confidence"])
        annotated = ai.annotate_frame(img, dets)
    else:
        state.update(None, 0.0)
        annotated = img
    return av.VideoFrame.from_ndarray(annotated, format="bgr24")


# ── Pilih latihan (jika belum dipilih) ────────────────────────────────
lesson_id = st.session_state.get("practice_lesson_id")
lesson = service.get_lesson(lesson_id) if lesson_id else None

if lesson is None:
    ui.section_mini("Mulai Latihan Baru", "Pilih materi & latihan untuk dicoba dengan AI")
    col_a, col_b = st.columns([3, 2])
    with col_a:
        st.markdown('<div class="stat-card">', unsafe_allow_html=True)
        materials = service.get_materials()
        mat_options = {m["title"]: m for m in materials}
        mat_choice = st.selectbox("Pilih materi", list(mat_options.keys()))
        material = mat_options[mat_choice]
        lessons = service.get_lessons(material["id"])
        lesson_map = {f'{l["title"]} ({l["practice_target"] or l["target"]})': l for l in lessons}
        lesson_choice = st.selectbox("Pilih latihan", list(lesson_map.keys()))
        st.button(
            "▶ Mulai Latihan", type="primary", use_container_width=True,
            on_click=lambda: _choose_lesson(lesson_map[lesson_choice]["id"]),
        )
        st.markdown('</div>', unsafe_allow_html=True)
    with col_b:
        rec = recommendation.next_recommendation(uid)
        st.markdown(
            f'<div class="stat-card" style="margin-bottom:0.8rem">'
            f'<div style="font-weight:800">🤖 {rec["title"]}</div>'
            f'<div style="color:{ui.MUTED};margin-top:0.3rem">{rec["text"]}</div>'
            f'</div>',
            unsafe_allow_html=True,
        )
        st.markdown(
            f'<div class="stat-card">'
            f'<div style="font-weight:700">💡 Cara kerja</div>'
            f'<div style="color:{ui.MUTED};font-size:0.85rem;margin-top:0.3rem">'
            f'Arahkan telapak tangan ke kamera. AI mendeteksi huruf secara real-time; '
            f'huruf yang stabil akan tersusun otomatis menjadi kata.</div></div>',
            unsafe_allow_html=True,
        )

        st.divider()
        ui.section_mini("Challenge Hari Ini", "Selesaikan untuk +XP")
        challenges = service.challenge_status(uid)
        if challenges:
            for ch in challenges:
                prog = ch["progress"] or 0
                pct = min(100, int(prog / ch["target"] * 100)) if ch["target"] else 0
                done = " ✅" if ch["completed"] else ""
                st.markdown(
                    f'<div class="stat-card" style="padding:0.6rem 0.9rem;'
                    f'margin-bottom:0.4rem">'
                    f'<div style="font-weight:700;font-size:0.9rem">{ch["title"]}{done}</div>'
                    f'<div style="color:{ui.MUTED};font-size:0.8rem">'
                    f'{prog}/{ch["target"]} · +{ch["reward_xp"]} XP</div>'
                    + ui.xp_bar_html(pct, height=6) +
                    f'</div>',
                    unsafe_allow_html=True,
                )
        else:
            st.caption("Tidak ada challenge hari ini.")
    st.stop()

target = lesson["practice_target"] or lesson["target"]
mode = lesson["practice_mode"]
letters = list(target) if mode == "letter" else list(target.replace(" ", ""))

# ── Layar utama ────────────────────────────────────────────────────────
left, right = st.columns([3, 2])

with left:
    st.markdown(
        f'<div class="stat-card" style="text-align:center">'
        f'<div style="font-size:0.9rem;font-weight:700;color:{ui.MUTED}">{lesson["title"]}</div>'
        f'<div style="font-size:2.2rem;font-weight:800;color:{ui.PRIMARY}">{target}</div>'
        f'<div style="color:{ui.MUTED};font-size:0.85rem">{lesson["description"]}</div>'
        f'</div>',
        unsafe_allow_html=True,
    )
    try:
        webrtc_ctx = webrtc_streamer(
            key=f"practice-{lesson_id}",
            mode=WebRtcMode.SENDRECV,
            rtc_configuration=RTC_CONFIG,
            media_stream_constraints={"video": True, "audio": False},
            video_frame_callback=process_frame,
            async_processing=True,
        )
    except Exception:
        st.warning("Kamera tidak tersedia di sesi ini. Gunakan browser "
                   "yang mendukung WebRTC (Chrome/Edge).")
        webrtc_ctx = None

with right:
    status = practice_status
    st.markdown('<div class="stat-card" style="min-height:330px">', unsafe_allow_html=True)

    if status == "idle":
        st.markdown(
            f'<div style="text-align:center;padding:1.4rem 0">'
            f'<div style="font-size:2rem">🎬</div>'
            f'<div style="font-weight:700">Siap berlatih?</div>'
            f'<div style="color:{ui.MUTED};font-size:0.85rem;margin-top:0.4rem">'
            f'Kamera mendeteksi isyarat secara real-time. Huruf yang stabil '
            f'dengan confidence cukup akan tersimpan otomatis.</div></div>',
            unsafe_allow_html=True,
        )
        st.button("▶ Mulai Latihan", type="primary", use_container_width=True,
                  on_click=start_session)
        st.button("📚 Ganti Latihan", use_container_width=True, on_click=_reset_lesson)
    elif status == "running":
        if "practice_mem" not in st.session_state:
            # Sesi latihan tidak tersedia (mis. status tersisa setelah
            # session/reset) → kembalikan ke idle agar tidak crash.
            st.session_state["practice_status"] = "idle"
            st.markdown(
                f'<div style="text-align:center;padding:1.4rem 0">'
                f'<div style="font-size:2rem">🔄</div>'
                f'<div style="font-weight:700">Sesi latihan tidak tersedia</div>'
                f'<div style="color:{ui.MUTED};font-size:0.85rem;margin-top:0.4rem">'
                f'Mulai ulang latihan untuk melanjutkan.</div></div>',
                unsafe_allow_html=True,
            )
            st.button("▶ Mulai Latihan", type="primary", use_container_width=True,
                      on_click=start_session)
        else:
            mem = st.session_state["practice_mem"]
            snap = state.snapshot()
            det = snap["letter"]
            current = mem.current_letter
            now_ = time.time()

            # Capture: satu pass per rerun (0.6s) — huruf yang stabil & cocok
            if (det and current and det == current
                    and snap["confidence"] >= auto_capture_min
                    and now_ - mem.last_capture_at > 0.8):
                mem.capture(det, snap["confidence"])
                mem.last_capture_at = now_

            # Error detection: gestur salah dicatat (tidak memajukan progress).
            # Dedupe per huruf: huruf salah yang sama dicatat sekali saja sampai
            # siswa pindah ke huruf lain (mencegah inflasi data analisis guru).
            wrong_msg = ""
            if (det and current and det != current
                    and snap["confidence"] >= WRONG_CONF_MIN):
                wrong_msg = scoring.feedback_for(current, det, snap["confidence"])
                if (now_ - mem.last_wrong_at > WRONG_COOLDOWN_S
                        and (not mem.wrong_attempts
                             or mem.wrong_attempts[-1]["predicted"] != det)):
                    mem.wrong_attempts.append({
                        "expected": current, "predicted": det,
                        "confidence": snap["confidence"],
                    })
                    mem.last_wrong_at = now_

            done_word = "".join(mem.captures)
            attempts_so_far = len(mem.captures) + len(mem.wrong_attempts)
            acc_so_far = len(mem.captures) / max(1, attempts_so_far) * 100

            feedback = ""
            if mem.captures:
                i = min(len(mem.captures) - 1, len(letters) - 1)
                expected = letters[i]
                guessed = mem.captures[-1]
                feedback = scoring.feedback_for(expected, guessed, mem.confidences[-1])

            live = st.empty()
            with live.container():
                st.markdown(
                    f'<div style="text-align:center">'
                    f'<div style="color:{ui.MUTED};font-size:0.85rem">Huruf yang diharapkan</div>'
                    f'<div style="font-size:2.6rem;font-weight:800;color:{ui.PRIMARY}">'
                    f'{current or "—"}</div>'
                    f'<div style="font-size:0.85rem;color:{ui.MUTED}">terdeteksi: '
                    f'{det if det else "—"}</div>'
                    f'<div style="border-top:1px dashed {ui.CARD_BORDER};margin:0.7rem 0"></div>'
                    f'<div style="font-weight:800;letter-spacing:0.2em;font-size:1.05rem">'
                    f'{done_word or "…"}</div>'
                    f'<div style="color:{ui.MUTED}">target: {target}</div>'
                    f'</div>'
                    + ui.xp_bar_html(len(done_word) / max(1, len(letters)) * 100, height=8)
                    + f'<div style="color:{ui.MUTED};font-size:0.8rem;margin-top:0.4rem">'
                    f'akurasi sementara: {acc_so_far:.0f}%</div>'
                    + (f'<div style="color:#dc2626;font-weight:700;margin-top:0.5rem">'
                       f'❌ Terdeteksi "{det}" — {wrong_msg}</div>' if wrong_msg
                       else (f'<div style="color:{ui.SECONDARY};font-weight:600;'
                             f'margin-top:0.4rem">✅ {feedback}</div>' if feedback and det else ""))
                    + '</div>',
                    unsafe_allow_html=True,
                )

            if not mem.in_progress():
                finish_session()
                st.stop()

            if webrtc_ctx and webrtc_ctx.state.playing:
                time.sleep(0.6)
                st.rerun()
            else:
                st.info("Mulai kamera di panel kiri (tombol Start) untuk memulai deteksi.")

    elif status == "done":
        res = st.session_state.get("last_result")
        if res:
            grade = res["assessment"]["grade"]
            st.markdown(
                f'<div style="text-align:center;padding:1rem 0">'
                f'<div style="font-size:2rem">🎉</div>'
                f'<div style="font-weight:800;font-size:1.2rem">Latihan selesai!</div>'
                f'<div style="font-size:2rem;font-weight:800;color:{ui.PRIMARY}">'
                f'{res["assessment"]["final"]:.0f}</div>'
                f'<div style="font-size:1.1rem">Grade <b>{grade}</b> · +10 XP</div>'
                f'</div>',
                unsafe_allow_html=True,
            )
        done_assignments = st.session_state.pop("last_assignment_completed", None)
        if done_assignments:
            st.success("✅ Tugas selesai otomatis: " + ", ".join(done_assignments))
        st.button("🔄 Ulangi Latihan", type="primary", use_container_width=True,
                  on_click=start_session)
        b1, b2 = st.columns(2)
        with b1:
            st.button("📚 Pilih Latihan Lain", use_container_width=True,
                      on_click=_reset_lesson)
        with b2:
            st.button("📈 Ke Progress", use_container_width=True,
                      on_click=lambda: st.switch_page("ui_pages/student_progress.py"))

    st.markdown('</div>', unsafe_allow_html=True)

    if status == "running":
        st.button("Akhiri & Lihat Nilai", type="secondary", use_container_width=True,
                  on_click=finish_session)

# ── Assessment Result (setelah selesai) ────────────────────────────────
if status == "done" and st.session_state.get("last_result"):
    res = st.session_state["last_result"]
    a = res["assessment"]

    st.divider()
    ui.section_header(f"Hasil Latihan: {target}", "Smart Assessment")

    c1, c2 = st.columns([1, 2])
    with c1:
        st.markdown(
            f'<div class="stat-card" style="text-align:center;padding:1.8rem">'
            f'<div style="color:{ui.MUTED};font-size:0.9rem;font-weight:600">FINAL SCORE</div>'
            f'<div style="font-size:3.4rem;font-weight:800;color:{ui.PRIMARY}">'
            f'{a["final"]:.0f}</div>'
            f'<div style="font-size:1.2rem;font-weight:800">Grade <b>{a["grade"]}</b></div>'
            f'<div style="color:{ui.SECONDARY};font-weight:700;font-size:0.95rem;margin-top:0.5rem">'
            f'+10 XP 🎉</div>'
            f'</div>',
            unsafe_allow_html=True,
        )
    with c2:
        m1, m2 = st.columns(2)
        with m1:
            ui.stat_card("Accuracy", f'{a["accuracy"]:.0f}%')
            ui.stat_card("Speed", f'{a["speed"]:.0f}%')
        with m2:
            ui.stat_card("Consistency", f'{a["consistency"]:.0f}%')
            ui.stat_card("Completion", f'{a["completion"]:.0f}%')

    st.divider()
    ui.section_mini("Rekomendasi", "Dari hasil latihan ini")
    st.markdown(
        f'<div class="stat-card"><div style="color:{ui.MUTED}">{a["recommendation"]}</div></div>',
        unsafe_allow_html=True,
    )
    if a.get("wrong_count"):
        st.caption(f"⚠️ {a['wrong_count']} percobaan salah terdeteksi selama latihan. "
                   f"Perhatikan posisi telapak tangan dan jari agar lebih konsisten.")

    ui.section_mini("Detail Huruf", "Status tiap huruf pada latihan ini")
    detail_rows = []
    for i in range(len(letters)):
        expected = letters[i]
        got = res["captures"][i] if i < len(res["captures"]) else "—"
        ok = "✅" if got == expected else "❌"
        detail_rows.append({"Urutan": i + 1, "Yang diminta": expected,
                            "Terdeteksi": got, "Status": ok})
    st.table(detail_rows)
