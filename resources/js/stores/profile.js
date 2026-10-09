import { reactive, watch } from 'vue';

const today = () => new Date().toISOString().slice(0, 10);
const initial = {
    totalXp: 80,
    currentStreak: 5,
    longestStreak: 8,
    completedLessons: [],
    completedPractices: [],
    challengeProgress: 3,
    challengeClaimed: false,
    lastActiveDate: today(),
    displayName: 'Aji Pratama',
    activityByDate: {},
    system: 'SIBI',
};

function loadProfile() {
    try {
        const saved = localStorage.getItem('signteach-demo');
        return saved ? { ...initial, ...JSON.parse(saved) } : { ...initial };
    } catch {
        return { ...initial };
    }
}

export const profile = reactive(loadProfile());

watch(profile, (value) => localStorage.setItem('signteach-demo', JSON.stringify(value)), { deep: true });

function recordActivity(xp) {
    const date = today();
    const difference = Math.round((Date.parse(`${date}T00:00:00Z`) - Date.parse(`${profile.lastActiveDate}T00:00:00Z`)) / 86400000);
    profile.currentStreak = difference === 0 ? profile.currentStreak : difference === 1 ? profile.currentStreak + 1 : 1;
    profile.longestStreak = Math.max(profile.longestStreak, profile.currentStreak);
    profile.totalXp += xp;
    profile.lastActiveDate = date;
    profile.activityByDate[date] = (profile.activityByDate[date] ?? 0) + 1;
}

export const actions = {
    setSystem(system) { profile.system = system; },
    setName(name) { profile.displayName = name.trim() || initial.displayName; },
    finishLesson(id, xp = 10) {
        if (profile.completedLessons.includes(id)) return;
        recordActivity(xp);
        profile.completedLessons.push(id);
        profile.challengeProgress = Math.min(5, profile.challengeProgress + 1);
    },
    finishPractice(id, xp = 10) {
        if (profile.completedPractices.includes(id)) return;
        recordActivity(xp);
        profile.completedPractices.push(id);
        profile.challengeProgress = Math.min(5, profile.challengeProgress + 1);
    },
    finishChallenge() {
        if (profile.challengeProgress < 5 || profile.challengeClaimed) return;
        profile.totalXp += 20;
        profile.challengeClaimed = true;
    },
    reset() { Object.assign(profile, structuredClone(initial)); },
};

export const levels = [
    { name: 'Beginner', min: 0, max: 100 },
    { name: 'Explorer', min: 100, max: 300 },
    { name: 'Communicator', min: 300, max: 700 },
    { name: 'Inclusive Champion', min: 700, max: Infinity },
];

export const lessons = [
    { id: 'alphabet-a', title: 'Alfabet Dasar', subtitle: 'Huruf A', unit: 'Unit 1 · Alfabet', gesture: 'A', description: 'Kepalkan tangan dengan ibu jari berada di sisi telunjuk.' },
    { id: 'alphabet-b', title: 'Alfabet Lanjutan', subtitle: 'Huruf B', unit: 'Unit 1 · Alfabet', gesture: 'B', description: 'Rapatkan empat jari dan tekuk ibu jari ke telapak.' },
    { id: 'numbers', title: 'Mengenal Angka', subtitle: 'Angka 1–5', unit: 'Unit 2 · Angka', gesture: 'C', description: 'Belajar mengenali bentuk tangan untuk angka sederhana.' },
    { id: 'words', title: 'Kata Sehari-hari', subtitle: 'Salam dan sapaan', unit: 'Unit 3 · Kata Dasar', gesture: 'D', description: 'Sapaan sederhana membantu memulai percakapan.' },
    { id: 'sentences', title: 'Kalimat Sederhana', subtitle: 'Susun kalimat', unit: 'Unit 4 · Komunikasi Dasar', gesture: 'E', description: 'Gabungkan tanda menjadi pesan yang mudah dipahami.' },
    { id: 'school', title: 'Di Sekolah', subtitle: 'Percakapan sekolah', unit: 'Unit 5 · Percakapan Sekolah', gesture: 'A', description: 'Berlatih percakapan yang sering digunakan di sekolah.' },
    { id: 'daily', title: 'Percakapan Sehari-hari', subtitle: 'Cerita harian', unit: 'Unit 6 · Percakapan', gesture: 'B', description: 'Gunakan bahasa isyarat dalam situasi sehari-hari.' },
];

export const lessonTitle = (lesson) => profile.system === 'BISINDO' ? `${lesson.title} BISINDO` : lesson.title;
