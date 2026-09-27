@extends('layouts.app')

@section('title', 'Inclusive Communication - SignTeach')

@section('content')
<x-section-header title="Inclusive Communication" subtitle="Jembatan Komunikasi Dua Arah: Isyarat ⇄ Suara & Teks 💬" />

<div class="card" style="margin-bottom:1.6rem;padding:0.6rem 0.8rem">
    <div style="display:flex;gap:0.8rem">
        <button id="tabSignToTextBtn" class="btn" onclick="switchInclusiveTab('signtotext')">
            ✋ Isyarat → Suara (Sign to Voice)
        </button>
        <button id="tabTextToSignBtn" class="btn btn-secondary" onclick="switchInclusiveTab('texttosign')">
            🗣️ Suara / Teks → Isyarat (Voice to Sign)
        </button>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     TAB 1: SIGN TO TEXT & SPEECH (Kamera Isyarat -> Teks -> TTS Suara)
     ════════════════════════════════════════════════════════════════════════ --}}
<div id="tabSignToText">
    <div style="display:grid;grid-template-columns:3fr 2fr;gap:1.6rem">
        {{-- Sisi Kiri: Kamera & Deteksi AI --}}
        <div>
            <div style="position:relative;width:100%;aspect-ratio:4/3;background:#0f172a;border-radius:var(--radius);overflow:hidden">
                <video id="incVideo" autoplay playsinline muted style="width:100%;height:100%;object-fit:cover;transform:scaleX(-1)"></video>
                <div id="incCamStatus" style="position:absolute;top:12px;left:12px;background:rgba(15,23,42,0.8);color:#fff;padding:4px 10px;border-radius:20px;font-size:0.8rem">
                    📷 Kamera Belum Aktif
                </div>
                <div id="incLiveDet" style="position:absolute;bottom:12px;right:12px;background:rgba(99,102,241,0.9);color:#fff;padding:6px 14px;border-radius:8px;font-size:1.2rem;font-weight:800;display:none">
                    -
                </div>
            </div>

            <div class="card" style="margin-top:1rem">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.8rem">
                    <div style="display:flex;gap:0.6rem">
                        <button id="btnIncStart" class="btn" onclick="startInclusiveCam()">📷 Aktifkan Kamera</button>
                        <button id="btnIncStop" class="btn btn-secondary" onclick="stopInclusiveCam()" style="display:none">Matikan Kamera</button>
                    </div>
                    <div class="caption">Isyaratkan huruf stabil di depan kamera</div>
                </div>

                {{-- Simulasi Alfabet Cepat (Uji Coba) --}}
                <div style="margin-top:0.8rem;padding-top:0.8rem;border-top:1px dashed var(--card-border)">
                    <div class="caption" style="margin-bottom:0.4rem;font-size:0.75rem">Input Huruf Cepat (Simulasi Deteksi):</div>
                    <div style="display:flex;flex-wrap:wrap;gap:0.25rem">
                        @foreach (['A','B','C','D','E','H','A','L','O','S','A','Y','A','T','E','R','I','M','A','K','A','S','I','H'] as $ch)
                            <button type="button" class="btn btn-secondary" style="padding:0.2rem 0.5rem;font-size:0.75rem" onclick="appendLetter('{{ $ch }}')">
                                {{ $ch }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Rangkaian Kalimat & Text-to-Speech --}}
        <div>
            <div class="card" style="min-height:480px;display:flex;flex-direction:column;justify-content:space-between">
                <div>
                    <x-section-mini title="Kalimat yang Tersusun" subtitle="Huruf isyarat dirangkai otomatis menjadi kata" />

                    <div style="background:#f7f6fd;border:1px solid var(--card-border);border-radius:var(--radius);padding:1.2rem;min-height:140px;margin-top:0.8rem">
                        <div id="composedText" style="font-size:1.8rem;font-weight:800;letter-spacing:0.05em;color:var(--text);word-wrap:break-word">
                            <span class="caption" style="font-weight:400;font-size:1rem">Belum ada huruf yang terdeteksi...</span>
                        </div>
                    </div>

                    <div style="display:flex;gap:0.6rem;margin-top:1rem;flex-wrap:wrap">
                        <button type="button" class="btn btn-secondary" style="flex:1" onclick="appendSpace()">␣ Spasi</button>
                        <button type="button" class="btn btn-secondary" style="flex:1" onclick="backspaceLetter()">⌫ Hapus</button>
                        <button type="button" class="btn btn-secondary" style="flex:1" onclick="clearText()">🗑️ Bersihkan</button>
                    </div>
                </div>

                <div style="margin-top:1.5rem">
                    <button type="button" class="btn btn-full" style="padding:0.9rem;font-size:1.1rem;background:#059669" onclick="speakComposedText()">
                        🔊 Ucapkan Suara (Text-to-Speech)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     TAB 2: TEXT/VOICE TO SIGN (Suara/Teks -> Visual Isyarat)
     ════════════════════════════════════════════════════════════════════════ --}}
<div id="tabTextToSign" style="display:none">
    <div class="card" style="margin-bottom:1.6rem">
        <x-section-mini title="Terjemahkan Suara / Teks ke Bahasa Isyarat" subtitle="Ketik kata atau ucapkan lewat mikrofon untuk melihat ejaan isyaratnya" />

        <div style="display:flex;gap:0.8rem;margin-top:1rem;align-items:center">
            <input type="text" id="textInputToSign" placeholder="Contoh: TERIMA KASIH, SAYA, KELUARGA" style="font-size:1.1rem;padding:0.7rem 1rem" oninput="translateToSign(this.value)">
            <button id="btnVoiceInput" type="button" class="btn" style="white-space:nowrap;padding:0.7rem 1.2rem" onclick="startSpeechRecognition()">
                🎤 Bicara
            </button>
        </div>
        <div id="voiceStatusText" class="caption" style="margin-top:0.4rem;color:var(--muted)"></div>
    </div>

    {{-- Tampilan Visual Isyarat Huruf-demi-Huruf --}}
    <x-section-mini title="Panduan Ejaan Isyarat (Fingerspelling SIBI)" subtitle="Peragakan gestur berikut sesuai urutan huruf" />
    <div id="signCardsContainer" style="display:flex;flex-wrap:wrap;gap:0.8rem;margin-top:0.8rem">
        {{-- Kartu huruf akan digenerate dinamis via JavaScript --}}
    </div>
</div>

<script>
    // Tab Switching
    function switchInclusiveTab(tab) {
        if (tab === 'signtotext') {
            document.getElementById('tabSignToText').style.display = 'block';
            document.getElementById('tabTextToSign').style.display = 'none';
            document.getElementById('tabSignToTextBtn').className = 'btn';
            document.getElementById('tabTextToSignBtn').className = 'btn btn-secondary';
        } else {
            document.getElementById('tabSignToText').style.display = 'none';
            document.getElementById('tabTextToSign').style.display = 'block';
            document.getElementById('tabSignToTextBtn').className = 'btn btn-secondary';
            document.getElementById('tabTextToSignBtn').className = 'btn';
            stopInclusiveCam();
            translateToSign(document.getElementById('textInputToSign').value || 'HALO');
        }
    }

    // ── Sign-to-Text State & Logic ────────────────────────────────────
    let currentSentence = '';
    let incStream = null;
    let incInterval = null;
    let stableHoldCount = 0;
    let lastSeenLetter = null;

    function renderSentence() {
        const box = document.getElementById('composedText');
        if (currentSentence.trim().length === 0) {
            box.innerHTML = '<span class="caption" style="font-weight:400;font-size:1rem">Belum ada huruf yang terdeteksi...</span>';
        } else {
            box.innerText = currentSentence;
        }
    }

    function appendLetter(char) {
        currentSentence += char;
        renderSentence();
    }

    function appendSpace() {
        if (currentSentence.length > 0 && !currentSentence.endsWith(' ')) {
            currentSentence += ' ';
            renderSentence();
        }
    }

    function backspaceLetter() {
        if (currentSentence.length > 0) {
            currentSentence = currentSentence.slice(0, -1);
            renderSentence();
        }
    }

    function clearText() {
        currentSentence = '';
        renderSentence();
    }

    function speakComposedText() {
        const text = currentSentence.trim();
        if (!text) {
            alert('Tidak ada teks untuk diucapkan.');
            return;
        }

        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 0.9;
            window.speechSynthesis.speak(utterance);
        } else {
            alert('Browser Anda tidak mendukung Text-to-Speech Web API.');
        }
    }

    async function startInclusiveCam() {
        const video = document.getElementById('incVideo');
        try {
            incStream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
            });
            video.srcObject = incStream;

            document.getElementById('btnIncStart').style.display = 'none';
            document.getElementById('btnIncStop').style.display = 'inline-block';
            document.getElementById('incCamStatus').innerText = '🟢 Kamera Aktif';

            startInclusiveDetection();
        } catch (e) {
            alert('Kamera tidak dapat diakses: ' + e.message);
        }
    }

    function stopInclusiveCam() {
        if (incStream) {
            incStream.getTracks().forEach(t => t.stop());
            incStream = null;
        }
        if (incInterval) {
            clearInterval(incInterval);
            incInterval = null;
        }
        document.getElementById('btnIncStart').style.display = 'inline-block';
        document.getElementById('btnIncStop').style.display = 'none';
        document.getElementById('incCamStatus').innerText = '📷 Kamera Dimatikan';
        document.getElementById('incLiveDet').style.display = 'none';
    }

    function startInclusiveDetection() {
        const video = document.getElementById('incVideo');
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        incInterval = setInterval(async () => {
            if (!incStream || video.readyState !== video.HAVE_ENOUGH_DATA) return;

            canvas.width = 320;
            canvas.height = 240;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(async (blob) => {
                if (!blob) return;
                const fd = new FormData();
                fd.append('frame', blob, 'frame.jpg');

                try {
                    const resp = await fetch('http://127.0.0.1:8100/detect?conf=0.35', {
                        method: 'POST',
                        body: fd
                    });
                    if (!resp.ok) return;
                    const data = await resp.json();
                    const dets = data.detections || [];
                    if (dets.length > 0) {
                        const best = dets.reduce((p, c) => c.confidence > p.confidence ? c : p);
                        document.getElementById('incLiveDet').style.display = 'block';
                        document.getElementById('incLiveDet').innerText = `${best.letter} (${Math.round(best.confidence * 100)}%)`;

                        // Stabilisasi huruf (hold 3 frames sebelum menambahkan)
                        if (best.letter === lastSeenLetter && best.confidence >= 0.55) {
                            stableHoldCount++;
                            if (stableHoldCount === 3) {
                                appendLetter(best.letter);
                            }
                        } else {
                            lastSeenLetter = best.letter;
                            stableHoldCount = 1;
                        }
                    } else {
                        document.getElementById('incLiveDet').style.display = 'none';
                        lastSeenLetter = null;
                        stableHoldCount = 0;
                    }
                } catch (e) {}
            }, 'image/jpeg', 0.8);
        }, 500);
    }

    // ── Voice / Text-to-Sign Logic ────────────────────────────────────
    function translateToSign(text) {
        const container = document.getElementById('signCardsContainer');
        container.innerHTML = '';

        const clean = (text || '').toUpperCase().trim();
        if (!clean) {
            container.innerHTML = '<div class="caption">Ketik kata di atas untuk melihat visual isyarat.</div>';
            return;
        }

        const words = clean.split(/\s+/);
        words.forEach((word) => {
            const wordGroup = document.createElement('div');
            wordGroup.style.display = 'flex';
            wordGroup.style.gap = '0.5rem';
            wordGroup.style.marginRight = '1.2rem';
            wordGroup.style.marginBottom = '0.8rem';
            wordGroup.style.alignItems = 'center';

            for (let i = 0; i < word.length; i++) {
                const char = word[i];
                const card = document.createElement('div');
                card.className = 'stat-card';
                card.style.textAlign = 'center';
                card.style.padding = '0.8rem 1rem';
                card.style.minWidth = '80px';
                card.style.background = '#ffffff';

                card.innerHTML = `
                    <div style="font-weight:800;font-size:1.8rem;color:var(--primary)">${char}</div>
                    <div class="caption" style="font-size:0.75rem;margin-top:0.2rem">Huruf SIBI</div>
                `;
                wordGroup.appendChild(card);
            }
            container.appendChild(wordGroup);
        });
    }

    function startSpeechRecognition() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            alert('Browser Anda belum mendukung input suara Speech Recognition.');
            return;
        }

        const recognizer = new SpeechRecognition();
        recognizer.lang = 'id-ID';
        recognizer.interimResults = false;

        document.getElementById('voiceStatusText').innerText = '🎙️ Mendengarkan suara Anda... Silakan berbicara.';
        document.getElementById('btnVoiceInput').innerText = '🔴 Mendengarkan...';

        recognizer.onresult = function(e) {
            const transcript = e.results[0][0].transcript;
            document.getElementById('textInputToSign').value = transcript;
            translateToSign(transcript);
            document.getElementById('voiceStatusText').innerText = `Terdeteksi: "${transcript}"`;
            document.getElementById('btnVoiceInput').innerText = '🎤 Bicara';
        };

        recognizer.onerror = function() {
            document.getElementById('voiceStatusText').innerText = 'Gagal mendeteksi suara atau dibatalkan.';
            document.getElementById('btnVoiceInput').innerText = '🎤 Bicara';
        };

        recognizer.onend = function() {
            document.getElementById('btnVoiceInput').innerText = '🎤 Bicara';
        };

        recognizer.start();
    }
</script>
@endsection
