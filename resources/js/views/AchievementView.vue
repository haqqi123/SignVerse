<script setup>
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { ArrowRight, Check, LockKeyhole, Sparkles, Trophy } from '@lucide/vue';
import { levels, profile } from '../stores/profile';

const filter = ref('all');
const badges = [
    { id: 'first-step', title: 'Langkah Pertama', description: 'Selesaikan lesson pertamamu', icon: '🌱', goal: 1, value: () => profile.completedLessons.length },
    { id: 'alphabet-pro', title: 'Sahabat Alfabet', description: 'Selesaikan 3 lesson', icon: '🔤', goal: 3, value: () => profile.completedLessons.length },
    { id: 'steady', title: 'Konsisten Berlatih', description: 'Bangun streak belajar 3 hari', icon: '🔥', goal: 3, value: () => profile.currentStreak },
    { id: 'explorer', title: 'Penjelajah Isyarat', description: 'Raih 100 XP', icon: '🧭', goal: 100, value: () => profile.totalXp },
    { id: 'first-practice', title: 'Berani Mencoba', description: 'Coba gesture pertamamu', icon: '✋', goal: 1, value: () => profile.completedPractices.length },
    { id: 'pathfinder', title: 'Penjelajah Jalur', description: 'Selesaikan seluruh 7 lesson', icon: '🗺️', goal: 7, value: () => profile.completedLessons.length },
    { id: 'streak-seven', title: 'Seminggu Bersama', description: 'Pertahankan streak selama 7 hari', icon: '🌟', goal: 7, value: () => profile.currentStreak },
    { id: 'communicator', title: 'Komunikator', description: 'Kumpulkan 300 XP', icon: '💬', goal: 300, value: () => profile.totalXp },
];
const currentLevel = computed(() => levels.find((item) => profile.totalXp < item.max) ?? levels.at(-1));
const nextLevel = computed(() => levels[levels.indexOf(currentLevel.value) + 1]);
const items = computed(() => badges.map((item) => ({ ...item, progress: Math.min(item.goal, item.value()), earned: item.value() >= item.goal })));
const visible = computed(() => items.value.filter((item) => filter.value === 'all' || (filter.value === 'earned' ? item.earned : !item.earned)));
</script>

<template>
    <div class="achievement-page"><section class="achievement-hero"><div class="achievement-hero-copy"><span class="eyebrow mint-eyebrow">KOLEKSI PENCAPAIAN</span><h1>Setiap langkah<br/><span>pantas dirayakan.</span></h1><p>Kumpulkan lencana dari usaha, konsistensi, dan keberanianmu mencoba hal baru.</p><div class="achievement-hero-stats"><div><b>{{ items.filter(item => item.earned).length }}<small> / {{ items.length }}</small></b><span>Lencana terbuka</span></div><i></i><div><b>{{ profile.completedLessons.length }}<small> lesson</small></b><span>Sudah diselesaikan</span></div></div></div><div class="achievement-trophy-scene"><span class="trophy-ring ring-a"></span><span class="trophy-ring ring-b"></span><div class="trophy-pedestal"><Trophy :size="53"/></div><span class="trophy-tag">LEVEL {{ currentLevel.name.toUpperCase() }}</span></div></section>
        <section class="badge-progress-banner"><div class="badge-progress-icon">✦</div><div class="badge-progress-copy"><b>{{ nextLevel ? `${nextLevel.min - profile.totalXp} XP lagi ke ${nextLevel.name}` : 'Kamu mencapai level tertinggi!' }}</b><small>Terus belajar untuk membuka lencana dan level berikutnya.</small><div class="badge-level-track"><i :style="{ width: `${nextLevel ? (profile.totalXp - currentLevel.min) / (nextLevel.min - currentLevel.min) * 100 : 100}%` }"></i></div></div><div class="badge-progress-value">{{ profile.totalXp }}<small> / {{ nextLevel?.min ?? currentLevel.min }} XP</small></div></section>
        <div class="achievement-collection-heading"><div><span class="eyebrow">PERJALANANMU</span><h2>Koleksi lencana</h2><p>Lencana terbuka dan target yang sedang kamu kejar.</p></div><div class="achievement-filter" role="group" aria-label="Filter pencapaian"><button v-for="option in [{ key: 'all', label: 'Semua' }, { key: 'earned', label: 'Terbuka' }, { key: 'locked', label: 'Belum terbuka' }]" :key="option.key" :class="{ active: filter === option.key }" :aria-pressed="filter === option.key" @click="filter = option.key">{{ option.label }}</button></div></div>
        <div class="achievement-collection"><article v-for="(item,index) in visible" :key="item.id" class="badge-card card" :class="{ unlocked: item.earned }"><div class="badge-medal" :class="{ shiny: item.earned }"><span>{{ item.icon }}</span><i><Check v-if="item.earned" :size="11"/><LockKeyhole v-else :size="10"/></i></div><div class="badge-card-copy"><div class="badge-card-top"><span class="eyebrow">{{ item.earned ? 'LENCANA TERBUKA' : `MILESTONE 0${index+1}` }}</span><span class="badge-points">{{ item.earned ? '✦ TERCAPAI' : `${Math.round(item.progress/item.goal*100)}%` }}</span></div><h3>{{ item.title }}</h3><p>{{ item.description }}</p><div class="badge-meter"><i :style="{ width: `${item.progress/item.goal*100}%` }"></i></div><small>{{ item.earned ? 'Kamu berhasil membuka lencana ini!' : `${item.progress} / ${item.goal} menuju lencana` }}</small></div></article></div>
        <footer class="achievement-footnote">✦ Progres lencana ini tersimpan di perangkat untuk keperluan demo. <RouterLink to="/learn">Lanjut belajar <ArrowRight :size="14"/></RouterLink></footer>
    </div>
</template>
