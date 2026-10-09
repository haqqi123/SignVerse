<script setup>
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { ArrowRight, Pencil, RotateCcw, Save, Target, X } from '@lucide/vue';
import { actions, levels, profile } from '../stores/profile';

const editing = ref(false);
const draft = ref(profile.displayName);
const currentLevel = computed(() => levels.find((item) => profile.totalXp < item.max) ?? levels.at(-1));
function saveName() { actions.setName(draft.value); editing.value = false; }
function cancelEdit() { draft.value = profile.displayName; editing.value = false; }
function resetDemo() { if (window.confirm('Reset semua progress demo di perangkat ini?')) actions.reset(); }
</script>

<template>
    <div class="profile-page">
        <header class="profile-page-heading">
            <span class="eyebrow mint-eyebrow">SIGNTEACH · DEMO</span>
            <h1>Profil Pelajar</h1>
            <p>Ruang personal untuk perjalanan belajarmu.</p>
        </header>
        <section class="profile-card card">
            <div class="profile-hero">
                <div class="profile-avatar">{{ profile.displayName.trim().charAt(0).toUpperCase() || 'A' }}</div>
                <div class="profile-identity">
                    <span class="eyebrow">PELAJAR SIGNTEACH</span>
                    <form v-if="editing" class="name-edit-form" @submit.prevent="saveName">
                        <label class="sr-only" for="display-name">Nama profil</label>
                        <input id="display-name" v-model="draft" maxlength="32" autofocus/>
                        <button type="submit" aria-label="Simpan nama"><Save :size="16"/></button>
                        <button type="button" aria-label="Batal mengedit nama" @click="cancelEdit"><X :size="16"/></button>
                    </form>
                    <template v-else>
                        <h2>{{ profile.displayName }}</h2>
                        <button class="edit-name" @click="draft=profile.displayName;editing=true"><Pencil :size="13"/> Edit nama</button>
                    </template>
                    <span>Pelajar SignTeach</span>
                </div>
            </div>
            <div class="profile-fields">
                <div><span>Sistem belajar</span><b>{{ profile.system }}</b></div>
                <div><span>Level</span><b>{{ currentLevel.name }}</b></div>
                <div><span>Total XP</span><b>{{ profile.totalXp }} XP</b></div>
                <div><span>Lesson selesai</span><b>{{ profile.completedLessons.length }} lesson</b></div>
                <div><span>Streak terpanjang</span><b>{{ profile.longestStreak }} hari</b></div>
                <div><span>Gesture dipraktikkan</span><b>{{ profile.completedPractices.length }} gesture</b></div>
            </div>
            <RouterLink to="/learn" class="secondary-link">Lanjut belajar <ArrowRight :size="16"/></RouterLink>
            <div class="profile-challenge-short"><Target :size="16"/><span>Daily Challenge</span><b>{{ profile.challengeProgress }}/5</b><RouterLink to="/challenge" aria-label="Lihat Daily Challenge"><ArrowRight :size="15"/></RouterLink></div>
            <div class="reset-area"><button class="text-button" @click="resetDemo"><RotateCcw :size="15"/> Reset progress demo</button><small>Data demo tersimpan lokal di browser.</small></div>
        </section>
        <footer class="info-footnote">✦ SignTeach · Setiap tanda membuka cerita baru.</footer>
    </div>
</template>
