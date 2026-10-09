<script setup>
import { computed, ref } from 'vue';
import { Check, Hand, RotateCcw, Sparkles, Target } from '@lucide/vue';
import { actions, profile } from '../stores/profile';

const targets = [
    { letter: 'A', hand: '✊', hint: 'Kepalkan tangan, ibu jari berada di samping.' },
    { letter: 'B', hand: '🖐🏻', hint: 'Rapatkan empat jari, tekuk ibu jari.' },
    { letter: 'C', hand: '🤏🏻', hint: 'Lengkungkan jari dan ibu jari membentuk huruf C.' },
    { letter: 'D', hand: '☝🏻', hint: 'Angkat telunjuk, jari lain menyentuh ibu jari.' },
    { letter: 'E', hand: '✊🏻', hint: 'Tekuk semua jari ke arah telapak tangan.' },
];
const selected = ref(0);
const scanning = ref(false);
const result = ref(null);
const earned = ref(false);
const target = computed(() => targets[selected.value]);
const practicedCount = computed(() => targets.filter((item) => profile.completedPractices.includes(`practice-${profile.system}-${item.letter}`)).length);
function choose(index) { selected.value = index; result.value = null; earned.value = false; }
function detect() {
    if (scanning.value) return;
    scanning.value = true;
    result.value = null;
    window.setTimeout(() => {
        const success = Math.random() < 0.82;
        const id = `practice-${profile.system}-${target.value.letter}`;
        earned.value = success && !profile.completedPractices.includes(id);
        result.value = success ? 'success' : 'retry';
        scanning.value = false;
        if (success) actions.finishPractice(id, earned.value ? 10 : 0);
    }, 1300);
}
</script>

<template>
    <main class="practice-page practice-focused"><header class="practice-page-heading"><span class="eyebrow">AI PRACTICE · {{ profile.system }}</span><h1>Latih gesture</h1><p>Pilih satu huruf, ikuti panduan, lalu cocokkan bentuk tanganmu.</p></header>
        <div class="practice-dashboard-grid"><section class="practice-console card"><div class="practice-console-heading"><div><span class="eyebrow">SESI GESTURE</span><h2>Latihan mandiri</h2></div><span class="session-system">{{ profile.system }}</span></div>
            <div class="practice-target-select"><div><b>Pilih target</b><small>Mulai dari alfabet dasar</small></div><div class="target-pills" role="group" aria-label="Pilih target huruf"><button v-for="(item,index) in targets" :key="item.letter" type="button" class="target-pill" :class="{ selected: selected === index, practiced: profile.completedPractices.includes(`practice-${profile.system}-${item.letter}`) }" :aria-pressed="selected === index" @click="choose(index)"><span>{{ item.hand }}</span><b>{{ item.letter }}</b><Check v-if="profile.completedPractices.includes(`practice-${profile.system}-${item.letter}`)" :size="12"/></button></div></div>
            <div class="practice-studio" :class="{ 'is-scanning': scanning }"><div class="studio-topline"><span><i></i>{{ scanning ? 'MENGANALISIS GESTURE' : 'PANDUAN GESTURE' }}</span><span>{{ profile.system }}</span></div><div class="studio-frame"><div class="studio-guides"><i></i><i></i><i></i></div><span class="studio-hand">{{ target.hand }}</span><div class="studio-target-label"><small>TARGET</small><b>{{ target.letter }}</b></div><div class="studio-status">AREA LATIHAN · SIMULASI TANPA KAMERA</div><i class="studio-corner top-left"></i><i class="studio-corner top-right"></i><i class="studio-corner bottom-left"></i><i class="studio-corner bottom-right"></i></div><p class="studio-hint">{{ target.hint }}</p></div>
            <div v-if="result" class="studio-result" :class="{ retry: result === 'retry' }" role="status"><div class="result-title"><span><Check v-if="result === 'success'" :size="16"/><Hand v-else :size="16"/></span><div><b>{{ result === 'success' ? 'Gesture cocok!' : 'Coba sekali lagi' }}</b><small>{{ result === 'success' ? 'Bagus, bentuk tanganmu sesuai target.' : 'Perhatikan posisi jari lalu ulangi.' }}</small></div><strong v-if="result === 'success'">+{{ earned ? 10 : 0 }} XP</strong></div><div v-if="result === 'success'" class="practice-reward-line">{{ earned ? 'Huruf ini tercatat di progres alfabetmu.' : 'Huruf ini sudah pernah dicatat.' }}</div></div>
            <div class="practice-control-row"><button v-if="result === 'retry'" class="secondary-button" @click="detect"><RotateCcw :size="15"/> Coba lagi</button><button class="primary-button" :disabled="scanning" @click="detect"><Sparkles v-if="scanning" :size="15"/><Target v-else :size="15"/> {{ scanning ? 'Menganalisis…' : 'Periksa gesture' }}</button></div>
        </section>
        <aside class="practice-progress-card card"><span class="eyebrow">PROGRES ALFABET</span><div class="practice-progress-total"><b>{{ practicedCount }}</b><span> dari 5 huruf</span></div><div class="progress-track"><i :style="{ width: `${practicedCount / 5 * 100}%` }"></i></div><div class="practice-letter-list"><div v-for="item in targets" :key="item.letter" :class="{ complete: profile.completedPractices.includes(`practice-${profile.system}-${item.letter}`) }"><span>{{ item.letter }}</span><small>{{ profile.completedPractices.includes(`practice-${profile.system}-${item.letter}`) ? 'Sudah dicoba' : 'Belum dicoba' }}</small><Check v-if="profile.completedPractices.includes(`practice-${profile.system}-${item.letter}`)" :size="14"/></div></div></aside></div>
    </main>
</template>
