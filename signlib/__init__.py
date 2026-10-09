"""SignTeach - signlib package.

Kumpulan modul pendukung aplikasi Streamlit SignTeach:
- db:        database sqlite + seeding
- auth:      login/logout & session
- ai:        wrapper model YOLO (deteksi alfabet)
- practice_state: state bersama untuk callback WebRTC (thread-safe)
- scoring:   formula penilaian latihan
- gamification: XP, level, badge
- service:   query data aplikasi (dashboard, progress, guru)
- ui:        komponen UI reusable (light theme)
- recommendation: rekomendasi belajar berbasis data
"""

__version__ = "1.0.0"