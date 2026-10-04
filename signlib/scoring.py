"""Formula penilaian latihan (Smart Assessment).

Formula sederhana, transparan, dan mudah diubah:

    accuracy    = jumlah huruf benar / total percobaan * 100
                (total percobaan = huruf benar yang direkam + gestur salah yang terdeteksi)
                Catatan: data seed demo (db.py) memakai benar/target; sesi live memakai benar/percobaan.
    speed       = 100 jika rata-rata waktu per huruf <= 3.5 detik,
                menurun linear sampai 40 pada ~7.8 detik/huruf
    consistency = 100 - (selisih max-min confidence * 120), dibatasi 0..100
    completion  = huruf yang berhasil direkam / total huruf * 100
    final_score = 0.4*accuracy + 0.2*speed + 0.2*consistency + 0.2*completion
"""

GRADE_THRESHOLDS = [
    (90, "A"),
    (80, "B"),
    (70, "C"),
    (60, "D"),
    (0, "E"),
]

# Bobot komponen (mudah diubah)
W_ACC = 0.4
W_SPEED = 0.2
W_CONSISTENCY = 0.2
W_COMPLETION = 0.2


def _round(v):
    return round(v, 1)


def compute_assessment(letters, captures, confidences, durations_s, wrong_count=0):
    """Hitung assessment dari satu sesi latihan.

    letters      : daftar huruf target (contoh ["S","A","Y","A"])
    captures     : daftar prediksi benar yang berhasil direkam (<= len(letters))
    confidences  : confidence tiap prediksi
    durations_s  : waktu (detik) yang dihabiskan untuk tiap huruf
    wrong_count  : jumlah percobaan salah (gestur salah tidak memajukan progress).
                Accuracy dihitung terhadap total percobaan agar jujur.
    """
    total = max(1, len(letters))
    done = min(len(captures), total)
    correct = sum(1 for i in range(done) if captures[i] == letters[i])

    attempts = done + wrong_count
    accuracy = correct / max(1, attempts) * 100
    completion = done / total * 100

    if durations_s:
        avg_time = sum(durations_s) / len(durations_s)
        speed = min(100, max(40, 100 - max(0, (avg_time - 3.5)) * 14))
    else:
        avg_time = 0.0
        speed = 100.0

    if confidences:
        consistency = min(100, max(0, 100 - (max(confidences) - min(confidences)) * 120))
    else:
        consistency = 100.0

    final = (
        accuracy * W_ACC
        + speed * W_SPEED
        + consistency * W_CONSISTENCY
        + completion * W_COMPLETION
    )

    grade = "E"
    for threshold, g in GRADE_THRESHOLDS:
        if final >= threshold:
            grade = g
            break

    return {
        "accuracy": _round(accuracy),
        "speed": _round(speed),
        "consistency": _round(consistency),
        "completion": _round(completion),
        "final": _round(final),
        "grade": grade,
        "avg_time_per_letter": round(avg_time, 1),
        "letters": letters,
        "captures": captures,
        "correct_count": correct,
        "total_count": total,
    }


def recommendation(assessment):
    """Rekomendasi berbasis hasil latihan (rule-based, bukan AI generatif)."""
    acc = assessment["accuracy"]
    if acc >= 95:
        return "Pertahankan konsistensi gerakan dan lanjutkan ke materi berikutnya."
    if acc < 75:
        return "Latih kembali huruf yang sering salah: pastikan telapak tangan menghadap ke depan dan jari terbuka jelas."
    if assessment["speed"] < 70:
        return "Gerakanmu sudah cukup tepat, tapi coba lakukan lebih pelan dan mantap."
    if assessment["consistency"] < 70:
        return "Usahakan posisi tangan konsisten di setiap pengulangan."
    return "Terus berlatih secara rutin untuk mengunci memori gerakan."


def feedback_for(expected, predicted, confidence):
    """Feedback per-huruf yang mudah dipahami, berdasarkan hasil deteksi."""
    if predicted == expected:
        if confidence >= 0.8:
            return "Gerakan sudah tepat"
        return "Gerakan tepat, tapi usahakan lebih mantap agar akurasi naik"
    if confidence < 0.6:
        return "Gerakan terlalu cepat, lakukan sedikit lebih pelan"
    return "Posisi tangan kurang tepat, perhatikan telapak tangan dan jari"