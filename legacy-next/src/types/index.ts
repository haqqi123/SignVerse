export type SignSystem = 'SIBI' | 'BISINDO';
export type LessonStatus = 'completed' | 'current' | 'available' | 'locked';
export type Exercise = { id: string; kind: 'info' | 'choice' | 'select' | 'matching' | 'practice'; prompt: string; answer: string; choices?: string[]; detail?: string };
export type Lesson = { id: string; title: string; subtitle: string; unit: string; xp: number; exercises: Exercise[] };
export type GamificationProfile = { totalXp: number; currentStreak: number; longestStreak: number; completedLessons: string[]; completedPractices: string[]; challengeProgress: number; challengeClaimed: boolean; lastActiveDate: string; displayName: string; activityByDate: Record<string, number>; system: SignSystem };
export type User = { name: string; role: string; joined: string };
export type Achievement = { id: string; title: string; description: string; icon: string; requirement: number };
