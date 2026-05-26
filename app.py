import streamlit as st
import cv2
import numpy as np
from PIL import Image
from ultralytics import YOLO
import tempfile, os
from streamlit_webrtc import webrtc_streamer, VideoProcessorBase, WebRtcMode, RTCConfiguration
import av

# ── Config ──────────────────────────────────────────
st.set_page_config(
    page_title="SIBI AI - Deteksi Alfabet",
    page_icon="🤟",
    layout="wide",
    initial_sidebar_state="expanded"
)

# ── Custom CSS for Premium UI ────────────────────────
st.markdown("""
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');

    :root {
        --primary: #6366f1;
        --secondary: #a855f7;
        --bg: #0f172a;
        --card-bg: rgba(30, 41, 59, 0.7);
    }

    html, body, [data-testid="stAppViewContainer"] {
        font-family: 'Outfit', sans-serif;
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
        color: white;
    }

    .stMetric {
        background: var(--card-bg);
        padding: 15px;
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }

    .main-title {
        font-size: 3.5rem;
        font-weight: 800;
        background: linear-gradient(to right, #818cf8, #c084fc);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0;
    }

    .sub-title {
        font-size: 1.2rem;
        color: #94a3b8;
        margin-bottom: 2rem;
    }

    .glass-card {
        background: var(--card-bg);
        padding: 2rem;
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    /* Style sidebar */
    [data-testid="stSidebar"] {
        background-color: rgba(15, 23, 42, 0.95);
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Streamlit overrides */
    .stButton>button {
        border-radius: 12px;
        padding: 0.5rem 2rem;
        background: linear-gradient(to right, #6366f1, #a855f7);
        color: white;
        border: none;
        font-weight: 600;
        transition: all 0.3s;
    }
    .stButton>button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.5);
    }
    </style>
    """, unsafe_allow_html=True)

# ── Load Model ───────────────────────────────────────
@st.cache_resource
def load_model():
    return YOLO("best.pt")

model = load_model()

# ── Sidebar ──────────────────────────────────────────
with st.sidebar:
    st.image("https://cdn-icons-png.flaticon.com/512/3233/3233497.png", width=80)
    st.markdown("# Kontrol AI")
    conf_threshold = st.sidebar.slider("Kepercayaan (Confidence)", 0.1, 1.0, 0.25, 0.05)
    iou_threshold  = st.sidebar.slider("IoU Threshold", 0.1, 1.0, 0.45, 0.05)
    
    st.divider()
    st.markdown("### Tentang SIBI AI")
    st.info("Sistem ini menggunakan YOLOv8 untuk mendeteksi alfabet Sistem Isyarat Bahasa Indonesia secara real-time.")

# ── Header ───────────────────────────────────────────
st.markdown('<h1 class="main-title">🤟 SIBI Vision AI</h1>', unsafe_allow_html=True)
st.markdown('<p class="sub-title">Deteksi Alfabet Bahasa Isyarat secara Real-time & Presisi</p>', unsafe_allow_html=True)

# ── Real-time Video Callback ─────────────────────────
def video_frame_callback(frame: av.VideoFrame) -> av.VideoFrame:
    img = frame.to_ndarray(format="bgr24")
    
    # Inference with optimized size (imgsz=320) for better FPS
    results = model.predict(
        source=img,
        conf=conf_threshold,
        iou=iou_threshold,
        imgsz=320,
        verbose=False
    )[0]

    # Draw results
    annotated_img = results.plot()
    
    return av.VideoFrame.from_ndarray(annotated_img, format="bgr24")

# ── Main Content ─────────────────────────────────────
mode = st.tabs(["🎥 Real-time Stream", "📷 Ambil Foto", "📁 Upload Gambar"])

# ── TAB 1: REAL-TIME ──
with mode[0]:
    st.markdown('<div class="glass-card">', unsafe_allow_html=True)
    st.subheader("Live Detection")
    
    RTC_CONFIGURATION = RTCConfiguration(
        {"iceServers": [
            {"urls": ["stun:stun.l.google.com:19302"]},
            {"urls": ["stun:stun1.l.google.com:19302"]},
            {"urls": ["stun:stun2.l.google.com:19302"]},
            {"urls": ["stun:stun3.l.google.com:19302"]},
            {"urls": ["stun:stun4.l.google.com:19302"]},
        ]}
    )

    webrtc_streamer(
        key="sibi-live-stable",
        mode=WebRtcMode.SENDRECV,
        rtc_configuration=RTC_CONFIGURATION,
        media_stream_constraints={
            "video": True,
            "audio": False
        },
        video_frame_callback=video_frame_callback,
        async_processing=True,
    )
    st.markdown('</div>', unsafe_allow_html=True)

# ── Helper for Static Detection ──
def run_static_inference(image: Image.Image):
    results = model.predict(
        source=image, # Direct PIL to avoid RGB/BGR swap
        conf=conf_threshold,
        iou=iou_threshold,
        verbose=False
    )[0]

    annotated = results.plot()
    annotated_rgb = cv2.cvtColor(annotated, cv2.COLOR_BGR2RGB)

    detections = []
    for box in results.boxes:
        cls_id = int(box.cls[0])
        conf   = float(box.conf[0])
        label  = model.names[cls_id]
        detections.append((label, conf))

    return Image.fromarray(annotated_rgb), detections

# ── TAB 2: CAMERA INPUT ──
with mode[1]:
    st.markdown('<div class="glass-card">', unsafe_allow_html=True)
    img_file = st.camera_input("Ambil foto tangan Anda")
    if img_file:
        image = Image.open(img_file).convert("RGB")
        with st.spinner("Menganalisis..."):
            result_img, detections = run_static_inference(image)
        
        col1, col2 = st.columns(2)
        with col1:
            st.image(image, caption="Original", width="stretch")
        with col2:
            st.image(result_img, caption="Hasil Deteksi", width="stretch")
            
        if detections:
            st.success(f"Ditemukan {len(detections)} alfabet")
            for label, conf in detections:
                st.metric(label="Huruf Terdeteksi", value=label, delta=f"{conf:.0%}")
    st.markdown('</div>', unsafe_allow_html=True)

# ── TAB 3: UPLOAD ──
with mode[2]:
    st.markdown('<div class="glass-card">', unsafe_allow_html=True)
    uploaded = st.file_uploader("Pilih file gambar", type=["jpg", "jpeg", "png"])
    if uploaded:
        image = Image.open(uploaded).convert("RGB")
        with st.spinner("Mendeteksi..."):
            result_img, detections = run_static_inference(image)
        
        col1, col2 = st.columns(2)
        with col1:
            st.image(image, caption="Input", width="stretch")
        with col2:
            st.image(result_img, caption="Output", width="stretch")
            
        if detections:
            st.success("Analisis Selesai")
            cols = st.columns(len(detections))
            for i, (label, conf) in enumerate(detections):
                cols[i % len(cols)].metric(label="Huruf", value=label, delta=f"{conf:.0%}")
    st.markdown('</div>', unsafe_allow_html=True)

# ── Footer ────────────────────────────────────────────
st.markdown("<br><hr>", unsafe_allow_html=True)
st.caption("Developed with ❤️ by SIBI Vision AI Team • YOLOv8 Engine • High Performance Inference")