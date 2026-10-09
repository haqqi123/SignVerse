"""State bersama untuk callback WebRTC (thread-safe).

Callback video streamlit-webrtc berjalan di thread terpisah sehingga
tidak boleh menulis langsung ke st.session_state. Semua hasil deteksi
disimpan di objek global ini dengan lock, lalu dibaca oleh main thread.
"""

import threading
import time


class PracticeState:
    _lock = threading.Lock()

    def __init__(self):
        self._reset()

    def _reset(self):
        self.letter = None          # huruf terdeteksi saat ini (stabil)
        self.confidence = 0.0
        self.frame_count = 0
        self.last_update = 0.0
        self.running = False
        # rolling window stabilisasi
        self._hist = []

    def start(self):
        with self._lock:
            self._reset()
            self.running = True

    def stop(self):
        with self._lock:
            self.running = False

    def update(self, letter, confidence):
        """Dipanggil dari callback thread. Stabilisasi: huruf dipakai bila
        muncul di mayoritas N frame terakhir (menghindari flicker)."""
        with self._lock:
            if not self.running:
                return
            self._hist.append((letter, confidence))
            if len(self._hist) > 12:
                self._hist.pop(0)
            self.frame_count += 1
            self.last_update = time.time()
            if len(self._hist) < 6:
                self.letter = False
                return
            from collections import Counter
            best, count = Counter(h[0] for h in self._hist).most_common(1)[0]
            if count >= 4:
                confs = [h[1] for h in self._hist if h[0] == best]
                self.letter = best
                self.confidence = sum(confs) / len(confs)
            else:
                self.letter = False
                self.confidence = 0.0

    def snapshot(self):
        with self._lock:
            return {
                "letter": self.letter,
                "confidence": self.confidence,
                "running": self.running,
                "frame_count": self.frame_count,
            }


# instance tunggal (module-level), aman diakses dari thread manapun
state = PracticeState()


class SessionMemory:
    """Memori satu sesi latihan (target, huruf tersisa, hasil, timing)."""

    def __init__(self, letters, target_label):
        self.letters = list(letters)
        self.target_label = target_label
        self.captures = []
        self.confidences = []
        self.durations = []
        self.wrong_attempts = []   # gestur salah: {expected, predicted, confidence}
        self.last_capture_at = 0.0
        self.last_wrong_at = 0.0
        self.started_at = time.time()
        self._letter_started_at = time.time()

    @property
    def index(self):
        return len(self.captures)

    @property
    def current_letter(self):
        if self.index >= len(self.letters):
            return None
        return self.letters[self.index]

    @property
    def remaining(self):
        return len(self.letters) - self.index

    def in_progress(self):
        return self.index < len(self.letters)

    def capture(self, predicted, confidence):
        """Rekam satu huruf (dengan waktu tiap huruf)."""
        self.durations.append(time.time() - self._letter_started_at)
        self.captures.append(predicted)
        self.confidences.append(confidence)
        self._letter_started_at = time.time()
        return predicted, self.index - 1

    def duration_s(self):
        return time.time() - self.started_at

    def reset(self):
        self.__init__(self.letters, self.target_label)
        self.started_at = time.time()