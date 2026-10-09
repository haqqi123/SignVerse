"""Sign to Speech — memakai Web Speech API bawaan browser.

Tanpa service eksternal. Komponen HTML + JS via st.components.v1.html.
"""

import json
import re

import streamlit as st


def speak_button(text: str, key: str = "tts", label: str = "🔊 Putar Suara",
                 rate: float = 0.95, lang: str = "id-ID"):
    text = (text or "").strip()
    if not text:
        st.info("Belum ada teks untuk dibacakan.")
        return

    fn = "speak_" + re.sub(r"[^A-Za-z0-9]", "u", key)
    safe = json.dumps(text)
    js = f"""
    <script>
    function {fn}() {{
      const raw = {safe};
      const u = new SpeechSynthesisUtterance(raw);
      u.lang = "{lang}"; u.rate = {rate}; u.pitch = 1.0;
      window.speechSynthesis.cancel();
      window.speechSynthesis.speak(u);
    }}
    </script>
    <button onclick="{fn}()"
      style="background:#6366f1;color:#fff;border:none;border-radius:14px;
             padding:10px 22px;font-size:1rem;font-weight:600;cursor:pointer;
             font-family: Outfit, sans-serif">{label}</button>
    """
    st.components.v1.html(f"<div>{js}</div>", height=60)