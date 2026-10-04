"""Mode bebas Sign-to-Text (dipakai AI Practice tab & Inclusive Room).

Alur: kamera → YOLO (recognition engine yang sama dengan latihan) → huruf
stabil tersusun → teks → tombol suara (Web Speech API).
"""

import threading
import time

import av
import streamlit as st
from streamlit_webrtc import RTCConfiguration, WebRtcMode, webrtc_streamer

from signlib import ai, ui
from signlib.speech import speak_button

RTC_CONFIG = RTCConfiguration({
    "iceServers": [
        {"urls": ["stun:stun.l.google.com:19302"]},
        {"urls": ["stun:stun1.l.google.com:19302"]},
    ]
})

# settings dibaca live oleh thread callback
_SETTINGS = {"conf": 0.30, "hold": 10}

# sink hasil capture; diakses dari thread callback & main thread → pakai lock
_LOCK = threading.Lock()
_SINK = []
_HOLD = {"letter": None, "count": 0}


def _process(frame: av.VideoFrame) -> av.VideoFrame:
    img = frame.to_ndarray(format="bgr24")
    with _LOCK:
        conf_min = _SETTINGS["conf"]
    dets = ai.detect_frame(img, conf=conf_min)
    best = max(dets, key=lambda d: d["confidence"]) if dets else None

    letter = best["letter"] if best else None
    conf = best["confidence"] if best else 0.0

    with _LOCK:
        if letter == _HOLD["letter"]:
            _HOLD["count"] += 1
        else:
            _HOLD["letter"] = letter
            _HOLD["count"] = 1

        if letter and _HOLD["count"] >= _SETTINGS["hold"] and conf >= 0.55:
            if not _SINK or _SINK[-1] != letter:
                _SINK.append(letter)
            _HOLD["count"] = 0  # perlu "stabil baru" — huruf beda baru direkam

    annotated = ai.annotate_frame(img, dets) if dets else img
    return av.VideoFrame.from_ndarray(annotated, format="bgr24")


def render_free_mode(key: str, mode_title: str = "Mode Bebas"):
    """Kamera live + huruf terurut + history + tombol bicara."""
    ss = st.session_state
    if "free_letters" not in ss:
        ss["free_letters"] = []
    if "free_history" not in ss:
        ss["free_history"] = []

    ui.section_header(mode_title, "Sign → Text → Speech (browser)")

    with st.sidebar:
        conf_free = st.slider("Confidence minimum", 0.10, 0.60, 0.30, 0.05, key=f"{key}-conf")
        hold_free = st.slider("Frame stabil untuk merekam", 4, 20, 10, 2, key=f"{key}-hold")
        with _LOCK:
            _SETTINGS["conf"] = conf_free
            _SETTINGS["hold"] = hold_free

    left, right = st.columns([3, 2])
    with left:
        st.markdown(
            f'<div class="stat-card" style="text-align:center">'
            f'<div style="font-weight:800;font-size:1.1rem">✋ {mode_title}</div>'
            f'<div style="color:{ui.MUTED};font-size:0.85rem">Isyaratkan huruf di depan '
            f'kamera; huruf stabil akan tersusun menjadi kata.</div></div>',
            unsafe_allow_html=True,
        )
        try:
            ctx = webrtc_streamer(
                key=f"free-{key}",
                mode=WebRtcMode.SENDRECV,
                rtc_configuration=RTC_CONFIG,
                media_stream_constraints={"video": True, "audio": False},
                video_frame_callback=_process,
                async_processing=True,
            )
        except Exception as e:
            st.warning("Kamera tidak tersedia di sesi ini. "
                    "Gunakan browser yang mendukung WebRTC.")
            ctx = None

    with right:
        text = "".join(ss["free_letters"])
        st.markdown(
            f'<div class="stat-card" style="min-height:180px">'
            f'<div style="color:{ui.MUTED};font-weight:700;font-size:0.85rem">Detected Sign</div>'
            f'<div style="font-size:1.9rem;font-weight:800;color:{ui.PRIMARY};'
            f'letter-spacing:0.15em;word-break:break-word">{text or "…"}</div></div>',
            unsafe_allow_html=True,
        )
        if text:
            speak_button(text, key=f"speech_{key}")
        b1, b2, b3 = st.columns(3)
        with b1:
            st.button("⏪ Hapus huruf", use_container_width=True,
                    on_click=_backspace)
        with b2:
            st.button("✔ Simpan kata", use_container_width=True,
                    on_click=_save_word)
        with b3:
            st.button("🗑 Reset", use_container_width=True,
                    on_click=_reset_letters)

        st.divider()
        st.markdown('<div style="font-weight:800">History</div>', unsafe_allow_html=True)
        if ss["free_history"]:
            for w in reversed(ss["free_history"]):
                st.markdown(
                    f'<div class="stat-card" style="padding:0.5rem 0.9rem;'
                    f'margin-bottom:0.4rem;font-weight:700">{w}</div>',
                    unsafe_allow_html=True,
                )
        else:
            st.caption("Belum ada riwayat.")

    # siklus aktif: drain hasil callback ke session state lalu rerun
    if ctx and ctx.state.playing:
        drained = []
        with _LOCK:
            while _SINK:
                drained.append(_SINK.pop(0))
        if drained:
            ss["free_letters"].extend(drained)
        time.sleep(1.2)
        st.rerun()


def _backspace():
    if st.session_state["free_letters"]:
        st.session_state["free_letters"].pop()


def _save_word():
    ss = st.session_state
    word = "".join(ss["free_letters"]).strip()
    if word:
        ss["free_history"].append(word)
    ss["free_letters"] = []


def _reset_letters():
    st.session_state["free_letters"] = []