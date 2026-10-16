<?php

namespace App\Services\Contracts;

/**
 * Kontrak deteksi gesture — implementasi nyata menyusul di Phase 9
 * (AIRecognitionService yang memanggil Python service YOLOv8).
 */
interface GestureRecognitionService
{
    /**
     * Evaluasi satu frame latihan terhadap gesture yang diharapkan.
     *
     * @param  string  $expectedGesture  Label gesture target (lesson.gesture_label).
     * @param  string|null  $imageData  Data frame (base64/data-URI) — null pada mock mode.
     * @return array{recognized: ?string, confidence: int, correct: bool}
     */
    public function recognize(string $expectedGesture, ?string $imageData = null): array;
}
