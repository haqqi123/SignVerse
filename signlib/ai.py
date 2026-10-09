"""Wrapper model YOLO existing untuk SignTeach.

Menggunakan model yang SUDAH ADA (best.pt) - tidak membuat recognition engine
baru. Model mendeteksi huruf alfabet A-Z (SIBI fingerspelling).

Catatan kejujuran teknologi:
- Model = YOLOv8 deteksi alfabet (bukan MediaPipe/LSTM).
- Kata dibangun lewat ejaan huruf (contoh "SAYA" = S-A-Y-A).
"""

import os
from functools import lru_cache

import cv2
import numpy as np

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODEL_PATH = os.path.join(BASE_DIR, "best.pt")


@lru_cache(maxsize=1)
def load_model():
    """Load model YOLO (cached agar tidak load ulang tiap rerun)."""
    from ultralytics import YOLO
    return YOLO(MODEL_PATH)


@lru_cache(maxsize=1)
def label_map():
    return load_model().names


def detect_frame(frame_bgr, conf=0.25, iou=0.45, imgsz=320):
    """Deteksi huruf pada satu frame.

    Returns: list dict: {letter, confidence, xyxy}
    """
    model = load_model()
    results = model.predict(
        source=frame_bgr,
        conf=conf,
        iou=iou,
        imgsz=imgsz,
        verbose=False,
    )[0]
    detections = []
    for box in results.boxes:
        cls_id = int(box.cls[0])
        detections.append({
            "letter": model.names[cls_id],
            "confidence": float(box.conf[0]),
            "xyxy": [float(v) for v in box.xyxy[0]],
        })
    return detections


def annotate_frame(frame_bgr, detections):
    """Gambar kotak + label di frame (tanpa relayout streamlit)."""
    out = frame_bgr.copy()
    for d in detections:
        x1, y1, x2, y2 = [int(v) for v in d["xyxy"]]
        cv2.rectangle(out, (x1, y1), (x2, y2), (99, 102, 241), 2)
        label = f"{d['letter']} {d['confidence']:.0%}"
        (tw, th), baseline = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.6, 2)
        cv2.rectangle(out, (x1, y1 - th - baseline - 6), (x1 + tw + 8, y1), (99, 102, 241), -1)
        cv2.putText(out, label, (x1 + 4, y1 - baseline - 2),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 2)
    return out