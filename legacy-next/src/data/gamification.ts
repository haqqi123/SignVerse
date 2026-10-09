import type { Achievement } from '@/types';
export const xpRules = { lesson: 10, aiPractice: 10, dailyChallenge: 20, perfectBonus: 5 } as const;
export const levels = [{ name: 'Beginner', min: 0, max: 100 }, { name: 'Explorer', min: 100, max: 300 }, { name: 'Communicator', min: 300, max: 700 }, { name: 'Inclusive Champion', min: 700, max: Infinity }];
export const achievements: Achievement[] = [
 {id:'first-step',title:'Langkah Pertama',description:'Selesaikan lesson pertamamu',icon:'🌱',requirement:1},
 {id:'alphabet-pro',title:'Sahabat Alfabet',description:'Selesaikan 3 lesson',icon:'🔤',requirement:3},
 {id:'steady',title:'Konsisten Berlatih',description:'Bangun streak belajar 3 hari',icon:'🔥',requirement:3},
 {id:'explorer',title:'Penjelajah Isyarat',description:'Raih 100 XP',icon:'🧭',requirement:100},
 {id:'first-practice',title:'Berani Mencoba',description:'Coba gesture pertamamu',icon:'✋',requirement:1},
 {id:'pathfinder',title:'Penjelajah Jalur',description:'Selesaikan seluruh 7 lesson',icon:'🗺️',requirement:7},
 {id:'streak-seven',title:'Seminggu Bersama',description:'Pertahankan streak selama 7 hari',icon:'🌟',requirement:7},
 {id:'communicator',title:'Komunikator',description:'Kumpulkan 300 XP',icon:'💬',requirement:300},
];
