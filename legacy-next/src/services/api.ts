/** Real backend integration is intentionally disabled for this frontend demo. */
export const apiConfig = { baseUrl: process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:5000/api' };
// Future API: POST /auth/register, /auth/login; GET /auth/me; GET /learning/sections?system=;
// GET /units/:id, /lessons/:id, /lessons/:id/exercises; POST /practice/results;
// GET /gamification/profile, /challenges/today; POST /challenges/:id/claim;
// GET /achievements, /achievements/me, /progress/me, /progress/materials.
// Backend must own final XP, streak, completion, achievement and unlock decisions.
