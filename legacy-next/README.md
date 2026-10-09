# SignTeach frontend demo

Interactive Next.js prototype for learning SIBI and BISINDO. All lesson content, progression, XP, daily challenge, achievements, weekly activity and AI practice results are mock data stored in browser `localStorage` under `signteach-demo`.

## Run locally

```bash
npm install
npm run dev
```

Set `NEXT_PUBLIC_API_URL` using `.env.example` only when a backend becomes available. This frontend currently makes no API requests.

## Demo behavior

- `/` redirects to `/learn`.
- Complete a lesson from the learning path to add demo XP and unlock the next lesson.
- The AI Practice activity simulates detection and accuracy; it does not request camera access or run a recognition model.
- Use Profile → Reset progress demo to clear the local demo state.

## Backend integration notes

See `src/services/api.ts` for planned routes. Expected integration includes `POST /api/auth/register`, `POST /api/auth/login`, `GET /api/auth/me`; learning routes `GET /api/learning/sections?system=SIBI`, `GET /api/units/:id`, `GET /api/lessons/:id`, `GET /api/lessons/:id/exercises`; `POST /api/practice/results`; `GET /api/gamification/profile`; `GET /api/challenges/today` and `POST /api/challenges/:id/claim`; `GET /api/achievements`, `GET /api/achievements/me`; and `GET /api/progress/me`, `GET /api/progress/materials`.

The backend must own authoritative XP, level, streak, lesson completion, unlocks, achievement and challenge updates. The frontend should render the response, including AI prediction fields such as gesture, confidence, correctness and feedback. MediaPipe + LSTM is a future AI-team responsibility; this demo has no model or backend integration.
