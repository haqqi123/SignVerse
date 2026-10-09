import { lessonsBySystem } from '@/data/lessons';
import type { SignSystem } from '@/types';
export const mockLearningService = { getLessons: (system: SignSystem) => Promise.resolve(lessonsBySystem[system]), getLesson: (id: string, system: SignSystem) => Promise.resolve(lessonsBySystem[system].find(l => l.id === id)) };
