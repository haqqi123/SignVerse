<script setup>
import { computed, ref } from 'vue';
import { RouterLink, RouterView, useRoute } from 'vue-router';
import { BookOpen, ChartNoAxesColumnIncreasing, Flame, Hand, Menu, Sparkles, Star, Target, Trophy, UserRound, X, ArrowRight } from '@lucide/vue';
import { actions, levels, profile } from './stores/profile';

const route = useRoute();
const menuOpen = ref(false);
const nav = [
    { to: '/learn', label: 'Belajar', icon: BookOpen },
    { to: '/practice', label: 'AI Practice', icon: Hand },
    { to: '/challenge', label: 'Challenge', icon: Target },
    { to: '/achievement', label: 'Achievement', icon: Trophy },
    { to: '/progress', label: 'Progress', icon: ChartNoAxesColumnIncreasing },
    { to: '/profile', label: 'Profile', icon: UserRound },
];
const mobileNav = nav.filter((item) => item.to !== '/achievement');
const level = computed(() => levels.find((item) => profile.totalXp < item.max) ?? levels.at(-1));
const nextLevel = computed(() => levels[levels.indexOf(level.value) + 1]);
const levelXp = computed(() => profile.totalXp - level.value.min);
const levelRange = computed(() => nextLevel.value ? nextLevel.value.min - level.value.min : 100);

function activeLink(to) {
    return route.path === to || (to === '/learn' && route.path.startsWith('/lesson/'));
}
</script>

<template>
    <div class="app-shell">
        <button class="mobile-menu" aria-label="Buka navigasi" @click="menuOpen = true"><Menu :size="23" /></button>
        <button v-if="menuOpen" class="scrim" aria-label="Tutup navigasi" @click="menuOpen = false"></button>
        <aside class="sidebar" :class="{ 'sidebar-open': menuOpen }">
            <div class="brand"><span class="brand-icon"><Hand :size="24" /></span><span>Sign<span class="brand-light">Teach</span><small>Belajar dengan tangan</small></span><button class="close-menu" aria-label="Tutup menu" @click="menuOpen = false"><X /></button></div>
            <div class="nav-label">RUANG BELAJAR</div>
            <nav><RouterLink v-for="item in nav" :key="item.to" :to="item.to" class="nav-link" :class="{ active: activeLink(item.to) }" @click="menuOpen = false"><component :is="item.icon" :size="19"/><span>{{ item.label }}</span><span v-if="item.to === '/learn'" class="nav-dot"></span></RouterLink></nav>
            <div class="sidebar-bottom"><div class="side-note"><Sparkles :size="18"/><p>Setiap tanda adalah awal dari percakapan baru.</p></div><div class="mini-user"><div class="avatar">{{ profile.displayName.charAt(0).toUpperCase() }}</div><div><b>{{ profile.displayName }}</b><small>Pelajar</small></div><span class="online-dot"></span></div></div>
        </aside>

        <main class="main-column">
            <header class="top-stats"><label class="system-select"><span class="system-symbol">✋</span><select aria-label="Pilih sistem bahasa isyarat" :value="profile.system" @change="actions.setSystem($event.target.value)"><option>SIBI</option><option>BISINDO</option></select></label><span class="stat-pill"><Flame class="flame" :size="17"/><b>{{ profile.currentStreak }}</b><small>streak</small></span><span class="stat-pill xp-pill"><Star :size="16"/><b>{{ profile.totalXp }}</b><small>XP</small></span><span class="level-pill"><span>✦</span>{{ level.name }}</span></header>
            <RouterView />
        </main>

        <aside class="right-panel"><div class="panel-heading">RUANG PROGRES <Sparkles :size="15"/></div>
            <section class="side-card streak-card"><div class="side-card-top"><span class="streak-icon"><Flame :size="21"/></span><span class="eyebrow">STREAK SAAT INI</span></div><div class="streak-number">{{ profile.currentStreak }} <small>hari</small></div><p>Keren! Jaga semangat belajarmu.</p><div class="week-dots"><span v-for="(day, index) in ['S','S','R','K','J','S','M']" :key="index" :class="{ 'day-done': index < profile.currentStreak % 7 }">{{ day }}</span></div></section>
            <RouterLink v-if="route.path !== '/challenge'" to="/challenge" class="side-card quest-card"><div class="side-card-top"><span class="quest-icon"><Target :size="20"/></span><span class="eyebrow">DAILY CHALLENGE</span><ArrowRight :size="16" class="muted-icon"/></div><b>Praktikkan 5 gesture</b><div class="quest-count">{{ profile.challengeProgress }} <span>/ 5 selesai</span></div><div class="progress-track"><i :style="{ width: `${profile.challengeProgress * 20}%` }"></i></div><small>🎁 +20 XP</small></RouterLink>
            <section class="side-card level-card"><div class="side-card-top"><span class="level-icon">✦</span><span class="eyebrow">LEVEL KAMU</span></div><div class="level-title">{{ level.name }}</div><div class="level-xp">{{ levelXp }} <span>/ {{ levelRange }} XP</span></div><div class="progress-track"><i class="purple-progress" :style="{ width: `${Math.min(100, levelXp / levelRange * 100)}%` }"></i></div><small>{{ nextLevel ? `${nextLevel.min - profile.totalXp} XP lagi menuju ${nextLevel.name}` : 'Level tertinggi tercapai!' }}</small></section>
            <div class="panel-quote">“Bahasa membuat kita terhubung.”<span>— Belajar hari ini, berteman selamanya</span></div>
        </aside>
        <nav class="mobile-bottom-nav" aria-label="Navigasi utama">
            <RouterLink v-for="item in mobileNav" :key="item.to" :to="item.to" :class="{ active: activeLink(item.to) }" :aria-label="item.label">
                <component :is="item.icon" :size="22"/>
                <span>{{ item.label }}</span>
            </RouterLink>
        </nav>
    </div>
</template>
