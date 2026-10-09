<script setup>
import { computed, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { ArrowRight, Camera, Check, Hand, RotateCcw, Sparkles, Star, X } from '@lucide/vue';
import { actions, lessons, lessonTitle, profile } from '../stores/profile';

const route = useRoute();
const router = useRouter();
const prefix = profile.system === 'BISINDO' ? 'bisindo-' : '';
const lessonId = computed(() => route.params.id.replace(/^bisindo-/, ''));
const lesson = computed(() => lessons.find((item) => item.id === lessonId.value));
const idForLesson = (item) => `${prefix}${item.id}`;
const step = ref(0);
const choice = ref('');
const checked = ref(false);
const scanning = ref(false);
const result = ref(null);
const wrong = ref(0);
const done = ref(false);
const earnedXp = ref(0);
const wasCompleted = computed(() => lesson.value && profile.completedLessons.includes(idForLesson(lesson.value)));
const lessonIndex = computed(() => lessons.findIndex((item) => item.id === lessonId.value));
const locked = computed(() => lessonIndex.value > 0 && lessons.slice(0, lessonIndex.value).some((item) => !profile.completedLessons.includes(idForLesson(item))));
const types = ['info', 'choice', 'select', 'matching', 'practice'];
const currentType = computed(() => types[step.value]);
const otherLetters = computed(() => ['A','B','C','D','E'].filter((letter) => letter !== lesson.value?.gesture).slice(0,3));
const options = computed(() => currentType.value === 'select' ? ['Gesture 1', `Gesture ${lesson.value?.gesture}`, 'Gesture 3'] : [lesson.value?.gesture, ...otherLetters.value]);
function advance() {
    if (step.value === types.length - 1) {
        const amount = wasCompleted.value ? 0 : 10 + (wrong.value === 0 ? 5 : 0);
        actions.finishLesson(idForLesson(lesson.value), amount);
        earnedXp.value = amount;
        done.value = true;
        return;
    }
    step.value += 1;
    choice.value = '';
    checked.value = false;
    result.value = null;
}
function checkChoice() {
    if (!choice.value) return;
    const answer = currentType.value === 'select' ? `Gesture ${lesson.value.gesture}` : lesson.value.gesture;
    if (choice.value !== answer) wrong.value += 1;
    checked.value = true;
}
function scan() {
    if (scanning.value) return;
    scanning.value = true;
    result.value = null;
    window.setTimeout(() => { result.value = Math.random() < 0.82 ? 'success' : 'retry'; scanning.value = false; if (result.value === 'retry') wrong.value += 1; }, 1200);
}
</script>

<template>
    <main v-if="!lesson" class="focus-layout"><section class="lesson-not-found"><h1>Lesson tidak ditemukan</h1><RouterLink to="/learn">Kembali ke jalur belajar</RouterLink></section></main>
    <main v-else-if="locked" class="focus-layout"><section class="locked-lesson"><div><X :size="32"/></div><span class="eyebrow">LESSON TERKUNCI</span><h1>Satu langkah dulu!</h1><p>Selesaikan lesson sebelumnya di jalur belajarmu untuk membuka materi ini.</p><RouterLink to="/learn" class="primary-button">KEMBALI KE JALUR <ArrowRight :size="17"/></RouterLink></section></main>
    <main v-else-if="done" class="focus-layout"><section class="result-screen"><div class="result-confetti">✦　✧　✦</div><div class="result-medal">🏆</div><span class="eyebrow mint-eyebrow">SATU LANGKAH LAGI!</span><h1>{{ wasCompleted ? 'Lesson selesai lagi!' : 'Hebat, kamu berhasil!' }}</h1><p>Lesson <b>{{ lessonTitle(lesson) }}</b> selesai. {{ wasCompleted ? 'XP sudah diperoleh sebelumnya.' : 'Teruskan perjalanan belajarmu.' }}</p><div class="reward-grid"><div><span class="reward-icon">⭐</span><b>+{{ earnedXp }} XP</b><small>{{ earnedXp ? 'XP diperoleh' : 'Sudah diperoleh' }}</small></div><div><span class="reward-icon">🎯</span><b>{{ wrong === 0 ? '100%' : '92%' }}</b><small>Akurasi latihan</small></div><div><span class="reward-icon">🔥</span><b>{{ profile.currentStreak }} hari</b><small>Streak saat ini</small></div></div><div class="total-xp-card"><span>Total XP</span><b>{{ profile.totalXp }} XP</b></div><div class="next-unlock"><span>🔓</span><div><b>Lesson berikutnya terbuka!</b><small>Jalur belajarmu terus bertambah.</small></div><Sparkles :size="19"/></div><button class="primary-button" @click="router.push('/learn')">LANJUTKAN <ArrowRight :size="18"/></button></section></main>
    <main v-else class="focus-layout"><header class="lesson-header"><RouterLink aria-label="Keluar dari lesson" to="/learn" class="exit-lesson"><X :size="21"/></RouterLink><div class="lesson-progress"><div><i :style="{ width: `${(step+1)/types.length*100}%` }"></i></div></div><span class="step-count">{{ step+1 }} <small>/ {{ types.length }}</small></span></header><section class="exercise-card"><div class="exercise-kicker"><span class="activity-badge"><Camera v-if="currentType==='practice'" :size="17"/><Hand v-else-if="currentType==='info'" :size="17"/><Star v-else :size="16"/></span><span>{{ currentType==='practice' ? 'AI PRACTICE · DEMO' : currentType==='info' ? 'KENALI MATERI' : currentType==='matching' ? 'COCOKKAN GESTURE' : 'LATIHAN' }}</span><span class="exercise-xp">+10 XP</span></div><h1>{{ currentType==='info' ? `Kenalan dengan ${lessonTitle(lesson)}` : currentType==='choice' ? `Tanda apakah ini? Petunjuk: ${lesson.description}` : currentType==='select' ? `Pilih gesture untuk ${lesson.gesture}` : currentType==='matching' ? 'Pasangkan tanda dengan huruf yang tepat' : `Praktikkan gesture ${lesson.gesture}` }}</h1>
        <template v-if="currentType==='info'"><div class="gesture-demo"><div class="gesture-hand">{{ lesson.gesture==='A' ? '✊🏻' : lesson.gesture==='B' ? '🖐🏻' : '🤟🏻' }}</div><span class="gesture-letter">{{ lesson.gesture }}</span><span class="gesture-caption">ILUSTRASI GESTURE · {{ profile.system }}</span></div><p class="exercise-detail">{{ lesson.description }}</p><button class="primary-button exercise-submit" @click="advance">LANJUTKAN <ArrowRight :size="18"/></button></template>
        <template v-else-if="currentType==='practice'"><div class="practice-target"><span>TARGET GESTURE</span><b>{{ lesson.gesture }}</b><small>{{ profile.system }} · {{ lesson.subtitle }}</small></div><div class="camera-preview" :class="{scanning}"><div class="camera-frame"><span class="corner tl"></span><span class="corner tr"></span><span class="corner bl"></span><span class="corner br"></span><div class="camera-hands">{{ scanning ? '🔎' : '✋🏻' }}</div><span class="camera-label">{{ scanning ? 'MENGANALISIS GESTURE...' : 'AREA KAMERA · DEMO' }}</span></div></div><div v-if="result" class="practice-feedback" :class="result"><b>{{ result==='success' ? 'Gesture dikenali ✓' : 'Gesture belum tepat' }}</b><div class="accuracy-row">Akurasi <span>{{ result==='success' ? '92%' : '58%' }}</span></div><div class="accuracy-track"><i :style="{width:result==='success'?'92%':'58%'}"></i></div><small>{{ result==='success' ? 'Posisi tangan sesuai · Gerakan stabil' : 'Coba arahkan tangan ke depan kamera.' }}</small></div><button v-if="!result" class="primary-button exercise-submit" :disabled="scanning" @click="scan"><Camera :size="18"/> {{ scanning ? 'MENDETEKSI...' : 'MULAI DETEKSI' }}</button><div v-else class="practice-actions"><button class="secondary-button" @click="scan"><RotateCcw :size="16"/> COBA LAGI</button><button class="primary-button" @click="advance">LANJUTKAN <ArrowRight :size="18"/></button></div><p class="demo-caption">Simulasi AI untuk demo · kamera asli tidak digunakan</p></template>
        <template v-else><div v-if="currentType==='matching'" class="matching-prompt"><span>✋🏻</span><b>Gesture {{ lesson.gesture }}</b><span class="match-arrow">→</span><small>Pilih huruf yang sesuai</small></div><div v-if="currentType==='select'" class="select-gesture-note">Pilih satu kartu gesture untuk melihat jawabannya.</div><div class="answer-grid"><button v-for="(item,index) in options" :key="item" class="answer-option" :class="{selected:choice===item,correct:checked&&item===(currentType==='select'?`Gesture ${lesson.gesture}`:lesson.gesture),incorrect:checked&&choice===item&&item!==(currentType==='select'?`Gesture ${lesson.gesture}`:lesson.gesture)}" @click="choice=item;checked=false"><span class="choice-letter">{{ String.fromCharCode(65+index) }}</span>{{ item }}</button></div><p v-if="checked" class="answer-feedback" :class="choice===(currentType==='select'?`Gesture ${lesson.gesture}`:lesson.gesture)?'good':'bad'">{{ choice===(currentType==='select'?`Gesture ${lesson.gesture}`:lesson.gesture) ? 'Tepat sekali!' : 'Belum tepat. Ingat kembali bentuk gesture-nya.' }}</p><button class="primary-button exercise-submit" :disabled="!choice||checked" @click="checkChoice">PERIKSA JAWABAN <Check :size="18"/></button><button v-if="checked" class="text-button continue-answer" @click="advance">LANJUTKAN <ArrowRight :size="16"/></button></template>
    </section><footer class="lesson-footnote">✦ Belajar dengan ritmemu sendiri. Kamu pasti bisa!</footer></main>
</template>
