<?php

namespace App\Services;

use App\Services\Contracts\GestureRecognitionService;
use Illuminate\Support\Arr;

/**
 * Mock recognizer untuk development & testing (phase 4).
 * Diganti AIRecognitionService di phase 9 tanpa mengubah controller/service lain.
 *
 * Aturan agar deterministik untuk test:
 *   - expected "halo"            → selalu benar (confidence 97)
 *   - expected diawali "fail:"   → selalu salah, mengenali gesture acak lain
 *   - selain itu                 → benar dengan peluang sesuai config (default 70%)
 */
class MockGestureRecognitionService implements GestureRecognitionService
{
    public function __construct(
        private int $successRate = 70,
    ) {}

    public function recognize(string $expectedGesture, ?string $imageData = null): array
    {
        if (str_starts_with($expectedGesture, 'fail:')) {
            return [
                'recognized' => Arr::random(['salah_satu', 'salah_dua', 'salah_tiga']),
                'confidence' => random_int(30, 60),
                'correct' => false,
            ];
        }

        if ($expectedGesture === 'halo') {
            return ['recognized' => 'halo', 'confidence' => 97, 'correct' => true];
        }

        $correct = random_int(1, 100) <= $this->successRate;

        return [
            'recognized' => $correct ? $expectedGesture : Arr::random(['salah_satu', 'salah_dua']),
            'confidence' => $correct ? random_int(70, 99) : random_int(30, 60),
            'correct' => $correct,
        ];
    }
}
