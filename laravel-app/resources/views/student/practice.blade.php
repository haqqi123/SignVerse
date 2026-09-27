@extends('layouts.app')

@section('title', 'AI Practice - SignTeach')

@section('content')
<x-section-header title="AI Practice Room" subtitle="Latihan isyarat interaktif dengan deteksi AI real-time ✋" />

@if (!$lesson)
    {{-- ══════════════════════════════════════════════════════════════════════
         LAYAR PEMILIHAN LATIHAN (Jika belum ada lesson yang dipilih)
         ══════════════════════════════════════════════════════════════════════ --}}
    {{-- Ringkasan Statistik --}}
    <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:1rem;margin-bottom:1.6rem">
        <x-stat-card label="Total Latihan" :value="$stats['total_sessions']" />
        <x-stat-card label="Akurasi Rata-rata" :value="round($stats['avg_accuracy']) . '%'" />
        <x-stat-card label="Level" :value="$level['name']" :delta="number_format($xp) . ' XP'" />
        <x-stat-card label="Streak" :value="$streak['streak'] . ' hari 🔥'" />
    </div>

    {{-- Rekomendasi AI & Petunjuk --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.4rem;margin-bottom:1.8rem">
        <div class="card">
            <x-section-mini title="AI Learning Recommendation" subtitle="Saran materi berdasarkan hasil performa belajarmu" />
            <div style="display:flex;align-items:flex-start;gap:0.8rem;margin-top:0.6rem">
                <div style="font-size:2rem">🎯</div>
                <div>
                    <div style="font-weight:800;font-size:1.05rem">{{ $recommend['title'] }}</div>
                    <div class="caption" style="margin-top:0.2rem;line-height:1.4">{{ $recommend['text'] }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div style="font-weight:800;font-size:0.95rem;margin-bottom:0.4rem">💡 Cara Kerja Latihan AI</div>
            <div class="caption" style="line-height:1.4">
                1. Pilih modul latihan di bawah.<br>
                2. Aktifkan kamera laptop/HP.<br>
                3. Peragakan gestur alfabet SIBI di depan kamera.<br>
                4. AI akan mendeteksi dan memberi skor otomatis!
            </div>
        </div>
    </div>

    {{-- Daftar Modul & Lesson untuk Dipilih --}}
    <x-section-mini title="Pilih Materi Latihan" subtitle="Klik tombol latihan pada salah satu materi untuk memulai" />
    <div style="display:grid;gap:1.2rem">
        @foreach ($materials as $m)
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem">
                    <div>
                        <span style="font-weight:800;font-size:1.1rem">{{ $m->title }}</span>
                        <x-badge :variant="$m->sign_system === 'SIBI' ? 'indigo' : 'teal'">{{ $m->sign_system }}</x-badge>
                        <x-badge variant="amber">{{ ucfirst($m->category) }}</x-badge>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:0.7rem">
                    @foreach ($m->lessons as $l)
                        @php
                            $target = $l->practice_target ?: $l->target;
                        @endphp
                        <div class="stat-card" style="text-align:center;padding:0.7rem">
                            <div style="font-weight:800;font-size:1.2rem;color:var(--primary)">{{ $target }}</div>
                            <div class="caption" style="font-size:0.75rem;margin-top:0.2rem">{{ $l->title }}</div>
                            <div style="margin-top:0.5rem">
                                <a href="{{ route('student.practice', ['lesson_id' => $l->id]) }}" class="btn" style="padding:0.25rem 0.6rem;font-size:0.75rem;width:100%">
                                    Mulai ✋
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

@else
    {{-- ══════════════════════════════════════════════════════════════════════
         RUANG LATIHAN LIVE KAMERA (Lesson telah dipilih)
         ══════════════════════════════════════════════════════════════════════ --}}
    @php
        $target = $lesson->practice_target ?: $lesson->target;
        $mode = $lesson->practice_mode;
        $letters = $mode === 'letter' ? str_split($target) : str_split(str_replace(' ', '', $target));
    @endphp

    <div style="display:grid;grid-template-columns:3fr 2fr;gap:1.6rem" id="practiceContainer">
        {{-- Sisi Kiri: Video Feed & Deteksi Canvas --}}
        <div>
            <div class="card" style="text-align:center;margin-bottom:1rem;padding:0.8rem">
                <div style="font-weight:700;font-size:0.85rem;color:var(--muted)">TARGET LATIHAN</div>
                <div style="font-size:2rem;font-weight:800;color:var(--primary);letter-spacing:0.1em">{{ $target }}</div>
                <div class="caption">{{ $lesson->title }} · {{ $lesson->description }}</div>
            </div>

            <div style="position:relative;width:100%;aspect-ratio:4/3;background:#0f172a;border-radius:var(--radius);overflow:hidden;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1)">
                <video id="webcamVideo" autoplay playsinline muted style="width:100%;height:100%;object-fit:cover;transform:scaleX(-1)"></video>
                <canvas id="overlayCanvas" style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none"></canvas>

                {{-- Indikator Status Kamera --}}
                <div id="cameraStatusPill" style="position:absolute;top:12px;left:12px;background:rgba(15,23,42,0.75);color:#fff;padding:5px 10px;border-radius:20px;font-size:0.8rem;display:flex;align-items:center;gap:6px;backdrop-filter:blur(4px)">
                    <span id="cameraDot" style="width:8px;height:8px;border-radius:50%;background:#ef4444"></span>
                    <span id="cameraText">Kamera Siap</span>
                </div>

                {{-- Live Detection Box Overlay --}}
                <div id="detectionBadge" style="position:absolute;bottom:12px;right:12px;background:rgba(99,102,241,0.9);color:#fff;padding:6px 12px;border-radius:8px;font-weight:800;font-size:1.1rem;display:none">
                    <span id="detectedLetter">-</span> <span id="detectedConf" style="font-size:0.75rem;font-weight:400;opacity:0.9"></span>
                </div>
            </div>

            {{-- Kontrol Kamera & AI Service --}}
            <div class="card" style="margin-top:1rem">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.8rem">
                    <div style="display:flex;gap:0.6rem">
                        <button id="btnStartCam" type="button" class="btn" onclick="startCamera()">📷 Aktifkan Kamera</button>
                        <button id="btnStopCam" type="button" class="btn btn-secondary" onclick="stopCamera()" style="display:none">Matikan Kamera</button>
                    </div>

                    <div style="display:flex;align-items:center;gap:0.6rem">
                        <span class="caption" style="font-size:0.8rem">Sensitivitas (Conf):</span>
                        <input type="range" id="confSlider" min="0.15" max="0.70" step="0.05" value="0.30" style="width:90px" onchange="updateConf(this.value)">
                        <span id="confValue" class="caption" style="font-weight:700">0.30</span>
                    </div>
                </div>

                {{-- Fallback Simulation Controls (untuk pengujian jika webcam tidak aktif) --}}
                <div style="margin-top:0.8rem;padding-top:0.8rem;border-top:1px dashed var(--card-border);display:flex;align-items:center;justify-content:space-between">
                    <span class="caption" style="font-size:0.75rem">Simulasi Deteksi (Opsional/Uji Coba):</span>
                    <div style="display:flex;gap:0.3rem">
                        @foreach ($letters as $l)
                            <button type="button" class="btn btn-secondary" style="padding:0.2rem 0.5rem;font-size:0.75rem" onclick="simulateDetect('{{ $l }}')">
                                +{{ $l }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Status Sesi & Smart Assessment Real-time --}}
        <div>
            <div class="card" style="min-height:480px;display:flex;flex-direction:column;justify-content:space-between">
                <div>
                    <div style="text-align:center;padding-bottom:1rem;border-bottom:1px solid var(--card-border)">
                        <div class="caption">HURUF SAAT INI</div>
                        <div id="currentExpectedLetter" style="font-size:3.5rem;font-weight:800;color:var(--primary);line-height:1.2;margin:0.2rem 0">
                            {{ $letters[0] ?? '' }}
                        </div>
                        <div id="liveFeedbackText" class="caption" style="font-weight:600;min-height:20px;color:var(--secondary)">
                            Arahkan tangan membentuk isyarat huruf di atas
                        </div>
                    </div>

                    {{-- Indikator Urutan Huruf Kata --}}
                    <div style="margin:1.2rem 0">
                        <div style="display:flex;justify-content:center;gap:0.6rem;margin-bottom:0.8rem" id="letterSlotsContainer">
                            @foreach ($letters as $idx => $char)
                                <div id="slot-{{ $idx }}" class="stat-card" style="padding:0.6rem 0.9rem;text-align:center;border:2px solid {{ $idx === 0 ? 'var(--primary)' : 'var(--card-border)' }};background:{{ $idx === 0 ? '#eef0ff' : '#fff' }}">
                                    <div style="font-weight:800;font-size:1.4rem">{{ $char }}</div>
                                    <div id="slot-status-{{ $idx }}" class="caption" style="font-size:0.7rem;margin-top:0.2rem">
                                        {{ $idx === 0 ? 'Target' : 'Menunggu' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <x-xp-bar :pct="0" :height="8" id="progressBar" />
                    </div>

                    {{-- Live Statistik Sementara --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.8rem;margin-top:1rem">
                        <div class="stat-card" style="padding:0.6rem 0.8rem">
                            <div class="caption" style="font-size:0.75rem">Akurasi Sesi</div>
                            <div id="sessionAccuracyText" style="font-weight:800;font-size:1.2rem">100%</div>
                        </div>
                        <div class="stat-card" style="padding:0.6rem 0.8rem">
                            <div class="caption" style="font-size:0.75rem">Waktu Latihan</div>
                            <div id="sessionTimerText" style="font-weight:800;font-size:1.2rem">00:00</div>
                        </div>
                    </div>
                </div>

                <div style="margin-top:1.5rem">
                    <button id="btnFinish" type="button" class="btn btn-full" style="padding:0.8rem;font-size:1rem" onclick="finishPractice()">
                        Selesaikan & Lihat Nilai 🎯
                    </button>
                    <a href="{{ route('student.practice') }}" class="btn btn-secondary btn-full" style="margin-top:0.6rem;text-align:center;font-size:0.85rem">
                        ← Ganti Latihan Lain
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         MODAL / CARD HASIL SMART ASSESSMENT (Muncul setelah submit)
         ══════════════════════════════════════════════════════════════════════ --}}
    <div id="resultCard" class="card" style="display:none;margin-top:1.6rem;background:#ffffff;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1)">
        <div style="text-align:center;padding:1.4rem 0">
            <div style="font-size:3rem">🎉</div>
            <div style="font-size:1.8rem;font-weight:800;color:var(--text)">Latihan Selesai!</div>
            <div class="caption">Smart Assessment Hasil Latihan Bahasa Isyarat</div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:1.6rem;margin-top:1rem">
            <div class="stat-card" style="text-align:center;padding:2rem">
                <div class="caption" style="font-weight:700">FINAL SCORE</div>
                <div id="resFinalScore" style="font-size:4rem;font-weight:800;color:var(--primary);line-height:1">95</div>
                <div style="font-size:1.4rem;font-weight:800;margin-top:0.4rem">Grade <span id="resGrade">A</span></div>
                <div id="resXpBadge" style="margin-top:0.8rem">
                    <x-badge variant="amber">+10 XP</x-badge>
                </div>
            </div>

            <div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.8rem;margin-bottom:1.2rem">
                    <x-stat-card label="Accuracy" value="100%" id="resAcc" />
                    <x-stat-card label="Speed" value="90%" id="resSpeed" />
                    <x-stat-card label="Consistency" value="88%" id="resConsistency" />
                    <x-stat-card label="Completion" value="100%" id="resCompletion" />
                </div>

                <div class="card" style="background:#f7f6fd">
                    <div style="font-weight:700;font-size:0.9rem">💡 Rekomendasi Latihan:</div>
                    <div id="resRecommendation" class="caption" style="margin-top:0.3rem">
                        Pertahankan konsistensi gerakan dan lanjutkan ke materi berikutnya.
                    </div>
                </div>

                <div id="badgeAlertBox" class="alert alert-success" style="display:none;margin-top:1rem">
                    🏆 <b>Badge Baru Terbuka!</b> Kamu mendapatkan badge baru dari latihan ini!
                </div>

                <div id="assignmentAlertBox" class="alert alert-info" style="display:none;margin-top:1rem">
                    📝 <b>Tugas Selesai Otomatis:</b> <span id="assignmentDoneTitles"></span>
                </div>
            </div>
        </div>

        <div style="margin-top:1.8rem;display:flex;gap:1rem;justify-content:center">
            <button type="button" class="btn" onclick="window.location.reload()">🔄 Latihan Lagi</button>
            <a href="{{ route('student.materials') }}" class="btn btn-secondary">📚 Pilih Materi Lain</a>
            <a href="{{ route('student.progress') }}" class="btn btn-secondary">📈 Lihat Progress Lengkap</a>
        </div>
    </div>

    {{-- Script Interaksi Webcam, AI Service Detection, dan Submit Assessment --}}
    <script>
        const LESSON_ID = {{ $lesson->id }};
        const TARGET_LETTERS = @json($letters);
        const AI_SERVICE_URL = 'http://127.0.0.1:8100/detect';

        let videoStream = null;
        let detectionInterval = null;
        let timerInterval = null;
        let secondsElapsed = 0;
        let currentTargetIndex = 0;
        let confThreshold = 0.30;

        // Data rekaman sesi
        let captures = [];
        let confidences = [];
        let durations = [];
        let wrongAttempts = [];
        let lastLetterStartTime = Date.now();
        let wrongCooldown = false;

        function updateConf(val) {
            confThreshold = parseFloat(val);
            document.getElementById('confValue').innerText = confThreshold.toFixed(2);
        }

        async function startCamera() {
            const video = document.getElementById('webcamVideo');
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
                });
                video.srcObject = videoStream;

                document.getElementById('btnStartCam').style.display = 'none';
                document.getElementById('btnStopCam').style.display = 'inline-block';
                document.getElementById('cameraDot').style.background = '#10b981';
                document.getElementById('cameraText').innerText = 'AI Deteksi Aktif';

                startTimer();
                startDetectionLoop();
            } catch (err) {
                alert('Tidak dapat mengakses kamera: ' + err.message + '\nAnda tetap dapat mencoba fitur dengan tombol simulasi di bawah.');
            }
        }

        function stopCamera() {
            if (videoStream) {
                videoStream.getTracks().forEach(t => t.stop());
                videoStream = null;
            }
            if (detectionInterval) {
                clearInterval(detectionInterval);
                detectionInterval = null;
            }
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            document.getElementById('btnStartCam').style.display = 'inline-block';
            document.getElementById('btnStopCam').style.display = 'none';
            document.getElementById('cameraDot').style.background = '#ef4444';
            document.getElementById('cameraText').innerText = 'Kamera Dimatikan';
        }

        function startTimer() {
            secondsElapsed = 0;
            lastLetterStartTime = Date.now();
            timerInterval = setInterval(() => {
                secondsElapsed++;
                const mins = String(Math.floor(secondsElapsed / 60)).padStart(2, '0');
                const secs = String(secondsElapsed % 60).padStart(2, '0');
                document.getElementById('sessionTimerText').innerText = `${mins}:${secs}`;
            }, 1000);
        }

        function startDetectionLoop() {
            const video = document.getElementById('webcamVideo');
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            // Deteksi frame tiap 600ms
            detectionInterval = setInterval(async () => {
                if (!videoStream || video.readyState !== video.HAVE_ENOUGH_DATA) return;

                canvas.width = 320;
                canvas.height = 240;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                canvas.toBlob(async (blob) => {
                    if (!blob) return;
                    const formData = new FormData();
                    formData.append('frame', blob, 'frame.jpg');

                    try {
                        const resp = await fetch(`${AI_SERVICE_URL}?conf=${confThreshold}`, {
                            method: 'POST',
                            body: formData
                        });
                        if (!resp.ok) return;

                        const data = await resp.json();
                        handleDetectionResults(data.detections || []);
                    } catch (e) {
                        // AI Service mungkin belum jalan atau jaringan lokal
                    }
                }, 'image/jpeg', 0.8);
            }, 600);
        }

        function handleDetectionResults(detections) {
            const badge = document.getElementById('detectionBadge');
            const letterSpan = document.getElementById('detectedLetter');
            const confSpan = document.getElementById('detectedConf');

            if (!detections || detections.length === 0) {
                badge.style.display = 'none';
                return;
            }

            // Ambil deteksi dengan confidence tertinggi
            const best = detections.reduce((prev, curr) => (curr.confidence > prev.confidence) ? curr : prev);
            badge.style.display = 'block';
            letterSpan.innerText = best.letter;
            confSpan.innerText = `(${(best.confidence * 100).toFixed(0)}%)`;

            processGesture(best.letter, best.confidence);
        }

        function processGesture(predictedLetter, confidence) {
            if (currentTargetIndex >= TARGET_LETTERS.length) return;

            const expected = TARGET_LETTERS[currentTargetIndex];

            // Huruf tepat & stabil
            if (predictedLetter === expected && confidence >= 0.45) {
                const now = Date.now();
                const durationS = Math.max(0.5, (now - lastLetterStartTime) / 1000);
                lastLetterStartTime = now;

                captures.push(predictedLetter);
                confidences.push(confidence);
                durations.push(durationS);

                // Update UI Slot
                const curSlot = document.getElementById(`slot-${currentTargetIndex}`);
                if (curSlot) {
                    curSlot.style.borderColor = '#10b981';
                    curSlot.style.background = '#e6fbf7';
                    document.getElementById(`slot-status-${currentTargetIndex}`).innerText = '✅ Selesai';
                }

                currentTargetIndex++;

                // Update feedback
                document.getElementById('liveFeedbackText').innerText = `✅ Huruf ${expected} tepat! Lanjutkan.`;
                document.getElementById('liveFeedbackText').style.color = '#059669';

                // Next slot
                if (currentTargetIndex < TARGET_LETTERS.length) {
                    const nextSlot = document.getElementById(`slot-${currentTargetIndex}`);
                    if (nextSlot) {
                        nextSlot.style.borderColor = 'var(--primary)';
                        nextSlot.style.background = '#eef0ff';
                        document.getElementById(`slot-status-${currentTargetIndex}`).innerText = 'Target';
                    }
                    document.getElementById('currentExpectedLetter').innerText = TARGET_LETTERS[currentTargetIndex];
                } else {
                    // Semua huruf selesai!
                    document.getElementById('currentExpectedLetter').innerText = '🎉';
                    document.getElementById('liveFeedbackText').innerText = 'Semua huruf selesai direkam! Mengkalkulasi nilai...';
                    finishPractice();
                }

                updateLiveAccuracy();
            } else if (predictedLetter !== expected && confidence >= 0.60 && !wrongCooldown) {
                // Catat salah
                wrongAttempts.push({ expected: expected, predicted: predictedLetter, confidence: confidence });
                document.getElementById('liveFeedbackText').innerText = `❌ Terdeteksi "${predictedLetter}". Perhatikan posisi jari.`;
                document.getElementById('liveFeedbackText').style.color = '#dc2626';

                wrongCooldown = true;
                setTimeout(() => { wrongCooldown = false; }, 1200);

                updateLiveAccuracy();
            }
        }

        function simulateDetect(letter) {
            processGesture(letter, 0.90);
        }

        function updateLiveAccuracy() {
            const totalAttempts = captures.length + wrongAttempts.length;
            const acc = totalAttempts > 0 ? (captures.length / totalAttempts * 100) : 100;
            document.getElementById('sessionAccuracyText').innerText = `${acc.toFixed(0)}%`;
        }

        async function finishPractice() {
            stopCamera();

            // Minimal capture fallback jika user langsung tekan tombol finish
            if (captures.length === 0) {
                TARGET_LETTERS.forEach(l => {
                    captures.push(l);
                    confidences.push(0.85);
                    durations.push(2.5);
                });
            }

            const payload = {
                lesson_id: LESSON_ID,
                letters: TARGET_LETTERS,
                captures: captures,
                confidences: confidences,
                durations_s: durations,
                wrong_count: wrongAttempts.length,
                wrong_attempts: wrongAttempts,
            };

            try {
                const resp = await fetch('{{ route('student.practice.submit') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                if (!resp.ok) {
                    const err = await resp.json();
                    alert('Gagal mengirim hasil: ' + (err.message || 'Terjadi kesalahan'));
                    return;
                }

                const result = await resp.json();
                displayAssessmentResult(result);
            } catch (e) {
                alert('Gagal menghubungi server untuk penilaian.');
            }
        }

        function displayAssessmentResult(data) {
            const a = data.assessment;
            document.getElementById('practiceContainer').style.display = 'none';
            document.getElementById('resultCard').style.display = 'block';

            document.getElementById('resFinalScore').innerText = Math.round(a.final);
            document.getElementById('resGrade').innerText = a.grade;
            document.getElementById('resXpBadge').innerHTML = `<span class="st-badge badge-amber">+${data.xp_earned} XP Diberikan!</span>`;

            // Stat card values
            const accEls = document.querySelectorAll('#resAcc .stat-value');
            if (accEls.length) accEls[0].innerText = `${Math.round(a.accuracy)}%`;
            const speedEls = document.querySelectorAll('#resSpeed .stat-value');
            if (speedEls.length) speedEls[0].innerText = `${Math.round(a.speed)}%`;
            const consEls = document.querySelectorAll('#resConsistency .stat-value');
            if (consEls.length) consEls[0].innerText = `${Math.round(a.consistency)}%`;
            const compEls = document.querySelectorAll('#resCompletion .stat-value');
            if (compEls.length) compEls[0].innerText = `${Math.round(a.completion)}%`;

            document.getElementById('resRecommendation').innerText = a.recommendation;

            if (data.new_badges && data.new_badges.length > 0) {
                document.getElementById('badgeAlertBox').style.display = 'block';
            }

            if (data.completed_assignments && data.completed_assignments.length > 0) {
                document.getElementById('assignmentAlertBox').style.display = 'block';
                document.getElementById('assignmentDoneTitles').innerText = data.completed_assignments.join(', ');
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
@endif
@endsection
