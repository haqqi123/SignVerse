<script setup>
import { RouterLink } from 'vue-router';
import { ArrowRight, CircleCheck, Sparkles } from '@lucide/vue';
import { actions, profile } from '../stores/profile';

const gestureSteps = ['A', 'B', 'C', 'D', 'E'];
</script>

<template>
    <div class="info-page challenge-page">
        <div class="challenge-welcome">
            <div class="challenge-welcome-copy">
                <div class="eyebrow mint-eyebrow">MISI HARI INI <span>·</span> SIGNTEACH</div>
                <h1>Siap kumpulkan <span>XP ekstra?</span></h1>
                <p>Selesaikan tantangan kecil hari ini. Belajar sedikit demi sedikit tetap berarti!</p>
            </div>
            <div class="challenge-mascot" aria-hidden="true"><span>✋</span><i>✦</i><b>GO!</b></div>
        </div>

        <section class="challenge-large card">
            <div class="challenge-card-heading">
                <span class="challenge-big-icon" aria-hidden="true">🎯</span>
                <div>
                    <span class="eyebrow">TANTANGAN HARIAN</span>
                    <h2>Praktikkan 5 gesture</h2>
                    <p>Coba lima gesture di sesi belajar atau AI Practice.</p>
                </div>
                <span class="challenge-stamp">+20 XP</span>
            </div>

            <div class="challenge-steps" aria-label="Progres gesture">
                <div v-for="(step, index) in gestureSteps" :key="step" class="challenge-step" :class="{ complete: index < profile.challengeProgress }">
                    <span class="challenge-step-icon"><CircleCheck v-if="index < profile.challengeProgress" :size="17"/><template v-else>{{ step }}</template></span>
                    <small>{{ index < profile.challengeProgress ? 'Selesai' : `Gesture ${index + 1}` }}</small>
                </div>
            </div>

            <div class="challenge-progress-label"><b>{{ profile.challengeProgress }} dari 5 gesture</b><span>{{ profile.challengeProgress >= 5 ? 'Hebat, semua selesai!' : `Tinggal ${5 - profile.challengeProgress} lagi` }}</span></div>
            <div class="progress-track big-track"><i :style="{ width: `${Math.min(100, profile.challengeProgress * 20)}%` }"></i></div>

            <div class="challenge-bottom-row">
                <div class="challenge-reward"><span>🎁</span><div><b>Hadiah tantangan</b><small>XP bisa dipakai untuk naik level</small></div><strong>+20 XP</strong></div>
                <div v-if="profile.challengeClaimed" class="claimed-note"><CircleCheck :size="18"/> Hadiah sudah diklaim!</div>
                <button v-else-if="profile.challengeProgress >= 5" class="primary-button" @click="actions.finishChallenge">KLAIM HADIAH <ArrowRight :size="18"/></button>
                <RouterLink v-else class="primary-button challenge-continue" to="/practice">LANJUT BERLATIH <ArrowRight :size="18"/></RouterLink>
            </div>
        </section>

        <div class="challenge-tip"><span>💡</span><p><b>Tips:</b> Ulangi gesture yang sudah dipelajari supaya makin lancar.</p></div>
    </div>
</template>
