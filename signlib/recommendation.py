"""AI Learning Path - rekomendasi berbasis data (rule-based, transparan).

Tidak menggunakan AI generatif; rekomendasi dihitung dari akurasi per
kategori materi pada data latihan nyata.
"""

from . import service


def next_recommendation(user_id):
    """Rekomendasi utama untuk dashboard siswa.

    Kategori dengan akurasi terendah (yang sudah pernah dicoba) menjadi
    prioritas latihan; jika kosong, sarankan mulai dari Alfabet.
    """
    cat = service.category_accuracy(user_id)
    labels = {"alfabet": "Alfabet", "angka": "Angka", "kosakata": "Kosakata Dasar"}

    if not cat:
        return {
            "title": "Mulai dari Alfabet",
            "text": "Belum ada data latihan. Mulailah dengan Alfabet SIBI untuk membangun dasar isyarat.",
            "category": "alfabet",
        }

    weakest = min(cat.items(), key=lambda kv: kv[1]["accuracy"])
    name, data = weakest
    acc = data["accuracy"]

    if acc < 85:
        text = (f"Latih kembali {labels.get(name, name)} karena akurasi rata-rata kamu "
                f"pada kategori ini masih {acc:.0f}%.")
    elif acc >= 95:
        text = (f"Kinerja {name} sudah sangat baik. Lanjutkan ke materi "
                f"berikutnya untuk variasi isyarat.")
    else:
        text = f"Pertahankan latihan {labels.get(name, name)} untuk menguasai kategori ini."

    return {
        "title": f"Direkomendasikan untukmu",
        "text": text,
        "category": name,
    }