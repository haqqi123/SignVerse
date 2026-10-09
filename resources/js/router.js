import { createRouter, createWebHistory } from 'vue-router';
import LearnView from './views/LearnView.vue';
import PracticeView from './views/PracticeView.vue';
import ChallengeView from './views/ChallengeView.vue';
import AchievementView from './views/AchievementView.vue';
import ProgressView from './views/ProgressView.vue';
import ProfileView from './views/ProfileView.vue';
import LessonView from './views/LessonView.vue';

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: '/learn' },
        { path: '/learn', component: LearnView },
        { path: '/practice', component: PracticeView },
        { path: '/challenge', component: ChallengeView },
        { path: '/achievement', component: AchievementView },
        { path: '/progress', component: ProgressView },
        { path: '/profile', component: ProfileView },
        { path: '/lesson/:id', component: LessonView },
        { path: '/:pathMatch(.*)*', redirect: '/learn' },
    ],
    scrollBehavior() { return { top: 0 }; },
});
