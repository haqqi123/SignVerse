# SignTeach AI Service

Microservice FastAPI untuk deteksi isyarat alfabet (YOLO `best.pt` existing).
Belum tersambung ke frontend Vue (deteksi di `PracticeView.vue` masih simulasi).

## Menjalankan

```bash
cd ai-service
pip install -r requirements.txt
uvicorn main:app --host 127.0.0.1 --port 8100
```

Cek: http://127.0.0.1:8100/health → `{"status": "ok", ...}`

## Endpoint

| Method | Path | Keterangan |
|--------|------|------------|
| GET | `/health` | Status model |
| POST | `/detect` | Multipart `frame` (JPEG/PNG), query `conf` (default 0.30) |

Contoh response `/detect`:

```json
{"detections": [{"letter": "S", "confidence": 0.87, "xyxy": [10, 20, 100, 120]}]}
```

Catatan: parameter deteksi conf/iou/imgsz=320. Versi lama (Streamlit dan
`laravel-app`) tersimpan di branch `archive/legacy`.
