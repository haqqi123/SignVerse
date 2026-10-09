<?php

namespace App\Services;

/*
 * Formula penilaian latihan (Smart Assessment) — paritas penuh dengan
 * signlib/scoring.py. Formula sederhana, transparan, dan mudah diubah:
 *
 *     accuracy    = jumlah huruf benar / total percobaan * 100
 *                   (total percobaan = huruf benar yang direkam + gestur salah)
 *     speed       = 100 jika rata-rata waktu per huruf <= 3.5 detik,
 *                   menurun linear sampai 40 pada ~7.8 detik/huruf
 *     consistency = 100 - (selisih max-min confidence * 120), dibatasi 0..100
 *     completion  = huruf yang berhasil direkam / total huruf * 100
 *     final_score = 0.4*accuracy + 0.2*speed + 0.2*consistency + 0.2*completion
 */
class ScoringService
{
    private const GRADE_THRESHOLDS = [
        90 => 'A',
        80 => 'B',
        70 => 'C',
        60 => 'D',
        0 => 'E',
    ];

    // Bobot komponen (mudah diubah)
    private const W_ACC = 0.4;
    private const W_SPEED = 0.2;
    private const W_CONSISTENCY = 0.2;
    private const W_COMPLETION = 0.2;

    /**
     * Hitung assessment dari satu sesi latihan.
     *
     * $letters      : daftar huruf target (contoh ["S","A","Y","A"])
     * $captures     : daftar prediksi benar yang berhasil direkam (<= huruf target)
     * $confidences  : confidence tiap prediksi
     * $durationsS   : waktu (detik) yang dihabiskan untuk tiap huruf
     * $wrongCount   : jumlah percobaan salah (gestur salah tidak memajukan progress).
     *                 Accuracy dihitung terhadap total percobaan agar jujur.
     *
     * Return array dengan kunci identik versi Python (accuracy, speed, ...,
     * grade, avg_time_per_letter, letters, captures, correct_count, total_count).
     */
    public static function computeAssessment(
        array $letters,
        array $captures,
        array $confidences,
        array $durationsS,
        int $wrongCount = 0,
    ): array {
        $total = max(1, count($letters));
        $done = min(count($captures), $total);
        $correct = 0;
        for ($i = 0; $i < $done; $i++) {
            if ($captures[$i] === $letters[$i]) {
                $correct++;
            }
        }

        $attempts = $done + $wrongCount;
        $accuracy = $correct / max(1, $attempts) * 100;
        $completion = $done / $total * 100;

        if ($durationsS !== []) {
            $avgTime = array_sum($durationsS) / count($durationsS);
            $speed = min(100, max(40, 100 - max(0, $avgTime - 3.5) * 14));
        } else {
            $avgTime = 0.0;
            $speed = 100.0;
        }

        if ($confidences !== []) {
            $consistency = min(100, max(0, 100 - (max($confidences) - min($confidences)) * 120));
        } else {
            $consistency = 100.0;
        }

        $final = $accuracy * self::W_ACC
            + $speed * self::W_SPEED
            + $consistency * self::W_CONSISTENCY
            + $completion * self::W_COMPLETION;

        $grade = 'E';
        foreach (self::GRADE_THRESHOLDS as $threshold => $g) {
            if ($final >= $threshold) {
                $grade = $g;
                break;
            }
        }

        return [
            'accuracy' => round($accuracy, 1),
            'speed' => round($speed, 1),
            'consistency' => round($consistency, 1),
            'completion' => round($completion, 1),
            'final' => round($final, 1),
            'grade' => $grade,
            'avg_time_per_letter' => round($avgTime, 1),
            'letters' => array_values($letters),
            'captures' => array_values($captures),
            'correct_count' => $correct,
            'total_count' => $total,
        ];
    }

    /**
     * Rekomendasi berbasis hasil latihan (rule-based, bukan AI generatif).
     */
    public static function recommendation(array $assessment): string
    {
        $acc = $assessment['accuracy'];
        if ($acc >= 95) {
            return 'Pertahankan konsistensi gerakan dan lanjutkan ke materi berikutnya.';
        }
        if ($acc < 75) {
            return 'Latih kembali huruf yang sering salah: pastikan telapak tangan menghadap ke depan dan jari terbuka jelas.';
        }
        if ($assessment['speed'] < 70) {
            return 'Gerakanmu sudah cukup tepat, tapi coba lakukan lebih pelan dan mantap.';
        }
        if ($assessment['consistency'] < 70) {
            return 'Usahakan posisi tangan konsisten di setiap pengulangan.';
        }

        return 'Terus berlatih secara rutin untuk mengunci memori gerakan.';
    }

    /**
     * Feedback per-huruf yang mudah dipahami, berdasarkan hasil deteksi.
     */
    public static function feedbackFor(string $expected, string $predicted, float $confidence): string
    {
        if ($predicted === $expected) {
            if ($confidence >= 0.8) {
                return 'Gerakan sudah tepat';
            }

            return 'Gerakan tepat, tapi usahakan lebih mantap agar akurasi naik';
        }
        if ($confidence < 0.6) {
            return 'Gerakan terlalu cepat, lakukan sedikit lebih pelan';
        }

        return 'Posisi tangan kurang tepat, perhatikan telapak tangan dan jari';
    }
}
