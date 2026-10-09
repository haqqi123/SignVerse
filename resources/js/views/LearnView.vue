<script setup>
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { ArrowRight, BookOpen, Clock3, LockKeyhole, Sparkles, X } from '@lucide/vue';
import { lessons, lessonTitle, profile } from '../stores/profile';

const selected = ref(null);
const sections = [
    { title: 'Dasar Bahasa Isyarat', description: 'Mulai dari bentuk tangan yang paling dasar', units: ['Unit 1 · Alfabet', 'Unit 2 · Angka', 'Unit 3 · Kata Dasar'], icon: '✋' },
    { title: 'Komunikasi Dasar', description: 'Rangkai tanda menjadi percakapan', units: ['Unit 4 · Komunikasi Dasar', 'Unit 5 · Percakapan Sekolah'], icon: '💬' },
    { title: 'Komunikasi Sehari-hari', description: 'Bawa keterampilanmu ke keseharian', units: ['Unit 6 · Percakapan'], icon: '🌿' },
];
const visibleLessons = computed(() => lessons.map((lesson) => ({ ...lesson, id: profile.system === 'BISINDO' ? `bisindo-${lesson.id}` : lesson.id })));
const completedCount = computed(() => visibleLessons.value.filter((lesson) => profile.completedLessons.includes(lesson.id)).length);
function statusOf(lesson) {
    if (profile.completedLessons.includes(lesson.id)) return 'completed';
    return visibleLessons.value.slice(0, visibleLessons.value.indexOf(lesson)).every((item) => profile.completedLessons.includes(item.id)) ? 'current' : 'locked';
}
</script>

<template>
    <div class="welcome-row"><div><div class="eyebrow mint-eyebrow">RUANG BELAJARMU <span class="sparkle-dot">✦</span></div><h1>Belajar bahasa isyarat,<br/><span>selangkah demi selangkah.</span></h1><p>Setiap gerakan tangan membuka cara baru untuk terhubung.</p></div><div class="welcome-art"><div class="art-sun"></div><span>✋🏻</span><i>Halo!</i></div></div>
    <section class="path-section"><div class="path-intro"><div><span class="eyebrow">JALUR BELAJAR · {{ profile.system }}</span><h2>Perjalananmu</h2></div><span class="path-count">{{ completedCount }} dari {{ visibleLessons.length }} lesson</span></div>
        <div class="learning-path"><div v-for="(section, sectionIndex) in sections" :key="section.title" class="path-chapter"><div class="chapter-header"><div class="chapter-icon">{{ section.icon }}</div><div><span class="eyebrow">BAGIAN 0{{ sectionIndex + 1 }}</span><h3>{{ section.title }}</h3><p>{{ section.description }}</p></div></div>
            <template v-for="(unit, unitIndex) in section.units" :key="unit"><div class="unit-label" :class="{ 'secondary-unit': unitIndex > 0 }">{{ unit }}</div><div class="node-track"><div v-for="lesson in visibleLessons.filter((item) => item.unit === unit)" :key="lesson.id" class="path-node-row" :class="[visibleLessons.indexOf(lesson) % 2 ? 'left' : 'right', statusOf(lesson)]"><button class="lesson-node" :class="statusOf(lesson)" :disabled="statusOf(lesson) === 'locked'" :aria-label="lessonTitle(lesson)" @click="selected = lesson"><span v-if="statusOf(lesson) === 'completed'">✓</span><LockKeyhole v-else-if="statusOf(lesson) === 'locked'" :size="17"/><BookOpen v-else :size="19"/></button><button class="node-copy" :class="statusOf(lesson)" :disabled="statusOf(lesson) === 'locked'" @click="selected = lesson"><span class="node-title">{{ lessonTitle(lesson) }}</span><span>{{ statusOf(lesson) === 'completed' ? 'Selesai' : statusOf(lesson) === 'current' ? 'MULAI' : `${lesson.subtitle} · 10 XP` }}</span></button></div></div></template>
        </div><div class="path-end">✦ &nbsp; Kamu sudah sampai di sini. Langkah kecil tetap berarti!</div></div>
    </section>
    <div v-if="selected" class="modal-backdrop" role="presentation" @click.self="selected = null"><section class="lesson-modal" role="dialog" aria-modal="true"><button class="modal-close" aria-label="Tutup" @click="selected = null"><X :size="19"/></button><div class="modal-illustration">✋🏻<span>✦</span></div><span class="eyebrow">{{ selected.unit.toUpperCase() }} · {{ profile.system }}</span><h2>{{ lessonTitle(selected) }}</h2><p>{{ selected.description }} Pelajari bentuk dasar, berlatih, dan coba gesture dengan simulasi AI.</p><div class="modal-meta"><span><Clock3 :size="16"/> 5 aktivitas</span><span><Sparkles :size="16"/> +10 XP</span></div><RouterLink class="primary-button modal-start" :to="`/lesson/${selected.id}`" @click="selected = null">MULAI BELAJAR <ArrowRight :size="18"/></RouterLink><div class="demo-caption">Konten dan XP demo · progres tersimpan di perangkat ini</div></section></div>
</template>
