"""Komponen UI reusable SignTeach (light theme).

Identitas visual: putih/very light lavender, indigo primary, teal secondary,
card rounded, border tipis, shadow halus. Mengganti dark glassmorphism lama.
"""

import streamlit as st

# ── Design tokens ──────────────────────────────────────────────────────
PRIMARY = "#6366f1"
PRIMARY_DARK = "#4f46e5"
SECONDARY = "#0ea5b7"
BG = "#f7f6fd"
CARD_BORDER = "#e5e4f0"
TEXT = "#1e1b3a"
MUTED = "#6b6a86"
RADIUS = "14px"

CSS = f"""
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap');

html, body, [data-testid="stAppViewContainer"], .stApp {{
    font-family: 'Outfit', -apple-system, sans-serif;
    background: {BG};
    color: {TEXT};
}}

[data-testid="stHeader"] {{ background: transparent; }}
[data-testid="stSidebar"] {{
    background: #ffffff;
    border-right: 1px solid {CARD_BORDER};
}}
[data-testid="stSidebar"] * {{ font-family: 'Outfit', sans-serif; }}

h1, h2, h3, h4 {{ font-family: 'Outfit', sans-serif; color: {TEXT}; }}

/* Card umum */
.st-card {{
    background: #ffffff;
    border: 1px solid {CARD_BORDER};
    border-radius: {RADIUS};
    padding: 1.2rem 1.4rem;
    box-shadow: 0 1px 3px rgba(30, 27, 58, 0.04);
}}

/* Stat card */
.stat-card {{
    background: #ffffff;
    border: 1px solid {CARD_BORDER};
    border-radius: {RADIUS};
    padding: 1rem 1.2rem;
    box-shadow: 0 1px 3px rgba(30, 27, 58, 0.04);
}}
.stat-card .stat-label {{ font-size: 0.8rem; color: {MUTED}; font-weight: 600; }}
.stat-card .stat-value {{ font-size: 1.6rem; font-weight: 800; color: {TEXT}; margin-top: 0.2rem; }}
.stat-card .stat-delta {{ font-size: 0.75rem; color: {SECONDARY}; font-weight: 600; }}

/* Button */
.stButton > button, [data-testid="stButton"] button {{
    background: {PRIMARY};
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 0.5rem 1.4rem;
    font-weight: 600;
    transition: all 0.15s;
}}
.stButton > button:hover, [data-testid="stButton"] button:hover {{
    background: {PRIMARY_DARK};
    color: #fff;
    transform: translateY(-1px);
}}

/* Input */
[data-testid="stTextInput"] input, [data-testid="stNumberInput"] input,
[data-testid="stDateInput"] input, [data-testid="stSelectbox"] > div > div,
[data-testid="stMultiSelect"] > div > div {{
    border-radius: 10px;
    border: 1px solid {CARD_BORDER};
}}

/* Tag/badge */
.st-badge {{
    display: inline-block;
    padding: 0.2rem 0.7rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}}
.badge-indigo {{ background: #eef0ff; color: {PRIMARY_DARK}; }}
.badge-teal {{ background: #e6fbf7; color: #0f766e; }}
.badge-amber {{ background: #fef3c7; color: #92400e; }}
.badge-gray {{ background: #f1f1f5; color: #52525b; }}

/* Hero landing */
.hero {{ text-align: center; padding: 2rem 0.5rem 3rem; }}
.hero h1 {{ font-size: 3rem; font-weight: 800; letter-spacing: -0.02em; }}
.hero .grad {{ background: linear-gradient(90deg, {PRIMARY}, {SECONDARY});
             -webkit-background-clip: text; -webkit-text-fill-color: transparent; }}
.hero .sub {{ color: {MUTED}; font-size: 1.15rem; margin-top: 0.4rem; }}

.section-title {{ font-size: 1.5rem; font-weight: 800; margin-bottom: 0.2rem; }}
.section-sub {{ color: {MUTED}; margin-bottom: 1.4rem; }}

/* XP bar */
.xp-track {{ background: #eef0ff; border-radius: 999px; height: 10px; overflow: hidden; }}
.xp-fill {{ background: linear-gradient(90deg, {PRIMARY}, {SECONDARY}); height: 10px; border-radius: 999px; }}

/* Progress ring */
.ring-wrap {{ display: flex; align-items: center; gap: 0.8rem; }}

/* Tab */
[data-testid="stTabs"] button {{ border-radius: 8px; font-family: 'Outfit', sans-serif; }}
[data-testid="stExpander"] {{ border: 1px solid {CARD_BORDER}; border-radius: {RADIUS}; }}

/* Metric & alert dirapikan */
[data-testid="stMetricValue"] {{ font-weight: 800; }}

/* Responsive */
@media (max-width: 768px) {{
    .hero h1 {{ font-size: 2.1rem; }}
    .stat-card .stat-value {{ font-size: 1.25rem; }}
}}

/* Video container constrain (streamlit-webrtc) */
video {{ border-radius: {RADIUS}; max-width: 100%; }}
[data-testid="stVideo"] {{ max-width: 100%; }}
</style>
"""


def inject_css():
    st.markdown(CSS, unsafe_allow_html=True)


def card(html_content, key=None):
    st.markdown(f'<div class="st-card">{html_content}</div>', unsafe_allow_html=True)


def stat_card(label, value, delta=None, key=None):
    d = f'<div class="stat-delta">{delta}</div>' if delta else ""
    st.markdown(
        f'<div class="stat-card"><div class="stat-label">{label}</div>'
        f'<div class="stat-value">{value}</div>{d}</div>',
        unsafe_allow_html=True,
    )


def badge(text, color="indigo"):
    cls = {"indigo": "badge-indigo", "teal": "badge-teal",
           "amber": "badge-amber", "gray": "badge-gray"}.get(color, "badge-gray")
    return f'<span class="st-badge {cls}">{text}</span>'


def xp_bar(progress_pct, height=10):
    pct = max(0, min(100, progress_pct))
    return (f'<div class="xp-track" style="height:{height}px">'
            f'<div class="xp-fill" style="width:{pct}%"></div></div>')


def xp_bar_html(progress_pct, label=None, height=10):
    pct = max(0, min(100, progress_pct))
    lbl = f'<div style="font-size:0.75rem;color:{MUTED};margin-bottom:4px">{label}</div>' if label else ""
    return (f'{lbl}<div class="xp-track" style="height:{height}px">'
            f'<div class="xp-fill" style="width:{pct}%"></div></div>')


def section_header(title, subtitle=None):
    st.markdown(f'<div class="section-title">{title}</div>', unsafe_allow_html=True)
    if subtitle:
        st.markdown(f'<div class="section-sub">{subtitle}</div>', unsafe_allow_html=True)


def section_mini(title, subtitle=None):
    st.markdown(f'<div style="font-weight:800;font-size:1.05rem">{title}</div>',
                unsafe_allow_html=True)
    if subtitle:
        st.markdown(f'<div style="color:{MUTED};font-size:0.85rem;margin-bottom:0.6rem">'
                    f'{subtitle}</div>', unsafe_allow_html=True)


def status_pill(status):
    """Pill untuk status (assignment, challenge)."""
    colors = {
        "belum dimulai": "gray",
        "sedang dikerjakan": "amber",
        "selesai": "teal",
    }
    return badge(status, colors.get(status, "gray"))


def empty_state(emoji, text):
    st.markdown(
        f'<div style="text-align:center;color:{MUTED};padding:2rem 0">'
        f'<div style="font-size:2rem">{emoji}</div><div>{text}</div></div>',
        unsafe_allow_html=True,
    )

