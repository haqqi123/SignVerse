"""
SignTeach AI Service — FastAPI wrapper untuk model YOLO existing (best.pt).

Paritas dengan signlib/ai.py (Python/Streamlit):
- Model deteksi alfabet A-Z SIBI fingerspelling TIDAK diubah.
- Parameter deteksi sama: conf (default 0.30), iou 0.45, imgsz 320.
- Return: [{letter, confidence, xyxy}] per deteksi.

Endpoint:
- GET  /health  -> status model
- POST /detect  -> multipart "frame" (JPEG/PNG); query: conf

Jalankan:
    pip install -r requirements.txt
    uvicorn main:app --host 127.0.0.1 --port 8100
"""

import io
import os

import cv2
import numpy as np
from fastapi import FastAPI, File, Query, UploadFile
from fastapi.middleware.cors import CORSMiddleware

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(os.path.dirname(BASE_DIR), "best.pt")

app = FastAPI(title="SignTeach AI Service", version="1.0.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
_model = None


def get_model():
    """Load model sekali (cache) — paritas load_model() di ai.py."""
    global _model
    if _model is None:
        from ultralytics import YOLO

        _model = YOLO(MODEL_PATH)
    return _model


@app.get("/health")
def health():
    try:
        names = get_model().names
        return {"status": "ok", "model": "best.pt", "classes": len(names)}
    except Exception as e:  # noqa: BLE001
        return {"status": "error", "detail": str(e)}


@app.post("/detect")
def detect(frame: UploadFile = File(...), conf: float = Query(0.30, ge=0.01, le=0.95)):
    """Deteksi huruf pada satu frame — paritas detect_frame() di ai.py."""
    data = np.frombuffer(frame.file.read(), np.uint8)
    img = cv2.imdecode(data, cv2.IMREAD_COLOR)
    if img is None:
        return {"detections": [], "error": "frame tidak valid"}

    model = get_model()
    results = model.predict(source=img, conf=conf, iou=0.45, imgsz=320, verbose=False)[0]

    detections = []
    for box in results.boxes:
        cls_id = int(box.cls[0])
        detections.append({
            "letter": model.names[cls_id],
            "confidence": float(box.conf[0]),
            "xyxy": [float(v) for v in box.xyxy[0]],
        })

    return {"detections": detections}
