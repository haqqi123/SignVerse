import { xpRules } from '@/data/gamification';
export const mockGamificationService = { completeLesson: (perfect = false) => xpRules.lesson + (perfect ? xpRules.perfectBonus : 0), completeChallenge: () => xpRules.dailyChallenge };
