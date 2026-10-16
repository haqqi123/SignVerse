<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-ink-muted">
            <a href="{{ route('student.practice.index') }}" class="hover:text-ink">AI Practice</a>
            <span>/</span>
            <span class="font-semibold text-ink">{{ $session->lesson->title }}</span>
        </div>
        <h2 class="section-title mt-1">Latihan: {{ $session->lesson->title }}</h2>
        <p class="section-sub">
            {{ $session->lesson->material->category->name }} · {{ strtoupper($session->language) }}
            · Target XP +{{ $session->lesson->xp_reward }}
        </p>
    </x-slot>

    <div class="py-8"
         x-data="practice({
             attemptUrl: '{{ route('student.practice.attempt', $session) }}',
             completeUrl: '{{ route('student.practice.complete', $session) }}',
             csrf: '{{ csrf_token() }}',
         })">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">

                {{-- Kamera --}}
                <div class="card lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h3 class="font-extrabold text-ink">Kamera Latihan</h3>
                        <span class="badge badge-amber !text-xs">Mock AI · Phase 9 nyata</span>
                    </div>

                    <div class="relative mt-4 aspect-video w-full overflow-hidden rounded-lg bg-gray-900">
                        <video x-ref="video" class="h-full w-full object-cover" autoplay playsinline muted></video>
                        <div x-show="!cameraActive" class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-gray-300">
                            <span class="text-4xl">📷</span>
                            <p class="text-sm">Kamera belum aktif</p>
                            <button @click="startCamera" class="btn-primary !py-2 text-sm">Aktifkan Kamera</button>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <button @click="takeAttempt" :disabled="busy" class="btn-primary flex-1 disabled:opacity-50">
                            <span x-show="!busy">✋ Periksa Isyaratku</span>
                            <span x-show="busy">Memeriksa…</span>
                        </button>
                        <button @click="completeSession" :disabled="busy" class="btn-secondary flex-1 disabled:opacity-50">
                            ✓ Akhiri & Lihat Hasil
                        </button>
                    </div>

                    <p class="mt-3 text-xs text-ink-muted">
                        Praktikkan isyarat
                        <code class="rounded bg-gray-100 px-1.5 py-0.5 font-bold">{{ $session->lesson->gesture_label }}</code>
                        di depan kamera, lalu tekan "Periksa Isyaratku".
                    </p>
                </div>

                {{-- Umpan balik langsung --}}
                <div class="space-y-4">
                    <div class="card text-center" :class="lastCorrect === true ? '!border-teal-300 !bg-teal-50' : (lastCorrect === false ? '!border-red-300 !bg-red-50' : '')">
                        <div class="stat-label">Umpan Balik</div>
                        <div class="mt-2 text-5xl" x-text="lastCorrect === true ? '✅' : (lastCorrect === false ? '❌' : '🤟')"></div>
                        <p class="mt-2 text-sm font-semibold text-ink" x-text="feedback"></p>
                        <p class="mt-1 text-xs text-ink-muted" x-text="attempts > 0 ? attempts + ' attempt di sesi ini' : 'Belum ada attempt'"></p>
                    </div>

                    <div class="card">
                        <h4 class="text-sm font-extrabold text-ink">Attempt Terakhir</h4>
                        <ul class="mt-2 space-y-1.5 text-sm">
                            @forelse ($results as $result)
                                <li class="flex items-center justify-between">
                                    <span class="{{ $result->correct ? 'text-teal-700' : 'text-red-600' }}">
                                        {{ $result->correct ? '✅' : '❌' }} {{ $result->recognized_gesture ?? '—' }}
                                    </span>
                                    <span class="text-xs text-ink-muted">{{ $result->confidence }}%</span>
                                </li>
                            @empty
                                <li class="text-ink-muted">Belum ada.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('practice', ({ attemptUrl, completeUrl, csrf }) => ({
                    cameraActive: false,
                    busy: false,
                    attempts: {{ $session->attempts }},
                    lastCorrect: null,
                    feedback: 'Siap mempraktikkan isyarat?',

                    async startCamera() {
                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({ video: true })
                            this.$refs.video.srcObject = stream
                            this.cameraActive = true
                        } catch (e) {
                            this.feedback = 'Kamera tidak dapat diakses — attempt tetap bisa dijalankan (mock mode).'
                            this.cameraActive = true
                        }
                    },

                    captureFrame() {
                        const video = this.$refs.video
                        if (!video.videoWidth) return null
                        const canvas = document.createElement('canvas')
                        canvas.width = video.videoWidth
                        canvas.height = video.videoHeight
                        canvas.getContext('2d').drawImage(video, 0, 0)
                        return canvas.toDataURL('image/jpeg', 0.8)
                    },

                    async takeAttempt() {
                        this.busy = true
                        try {
                            const res = await fetch(attemptUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ image: this.captureFrame() }),
                            })
                            const data = await res.json()
                            this.lastCorrect = data.correct
                            this.attempts = data.attempts
                            this.feedback = data.correct
                                ? `Benar! Terdeteksi "${data.recognized}" (${data.confidence}%)`
                                : `Terdeteksi "${data.recognized ?? 'tidak dikenali'}" — coba lagi!`
                        } finally {
                            this.busy = false
                        }
                    },

                    async completeSession() {
                        this.busy = true
                        window.location = completeUrl
                    },
                }))
            })
        </script>
    @endpush
</x-app-layout>
