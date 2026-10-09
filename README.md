# SignTeach

SignTeach is a Laravel 13 application with a Vue 3 single-page frontend bundled by Vite. It demonstrates SIBI and BISINDO learning paths, interactive lessons, simulated AI practice, XP, streaks, challenges, achievements, progress, and profile editing. Demo progress is stored in browser `localStorage`; gesture recognition is simulated and does not access the camera.

## Requirements

- PHP 8.3+
- Composer
- Node.js 20.19+ and npm

## First-time setup

```sh
composer install
copy .env.example .env
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

Start Laravel with `php artisan serve --port=3001`. For frontend hot reload, run `npm run dev` in a second terminal. Open [http://localhost:3001](http://localhost:3001).

## Pages

- `/learn` — SIBI/BISINDO learning path and lesson progression
- `/lesson/{id}` — lesson activities and completion rewards
- `/practice` — simulated gesture practice
- `/challenge`, `/achievement`, `/progress`, `/profile` — gamification and learner summary

The previous Next.js source is preserved in `legacy-next/` while the active app uses Laravel and Vue.
