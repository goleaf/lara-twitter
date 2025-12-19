# Repository Guidelines

## Project Structure & Module Organization

- `app/`: PHP application code (Laravel 12).
  - `app/Livewire/`: Livewire v3 page/components (e.g., timeline, profile, posts).
  - `app/Filament/`: Filament admin panel resources.
  - `app/Models/`, `app/Services/`, `app/Observers/`: domain logic and model lifecycle.
-  `app/Http/Requests/`: FormRequest validation for app actions (prefer adding rules here, not inline).
- `routes/`: route definitions (`routes/web.php`, `routes/auth.php` using Livewire Volt pages).
- `resources/`: frontend source.
  - `resources/views/`: Blade templates + Livewire/Volt views (see `resources/views/livewire/pages/auth/`).
  - Layout: use `resources/views/layouts/app.blade.php` as the single shared layout.
  - `resources/css/`, `resources/js/`: Vite entrypoints (`resources/css/app.css`, `resources/js/app.js`).
- `database/`: migrations, factories, seeders, and local SQLite file (`database/database.sqlite`).
- `tests/`: PHPUnit tests (`tests/Feature/`, `tests/Unit/`).
- `public/`: web root; built assets land in `public/build/` (Vite).

## Build, Test, and Development Commands

- `composer setup`: install deps, create `.env`, migrate/seed, link storage, install/build frontend.
- `composer dev`: runs `php artisan serve`, queue listener, log tailing (Pail), and `npm run dev` via `concurrently`.
- `composer test`: clears config cache then runs `php artisan test`.
- Frontend only: `npm run dev` (HMR), `npm run build` (production build).

## Coding Style & Naming Conventions

- Indentation: 4 spaces (see `.editorconfig`); keep files LF-terminated.
- PHP: follow Laravel conventions; classes `PascalCase`, methods/vars `camelCase`.
- Blade views: keep templates in `resources/views/` and name files descriptively (e.g., `post-card.blade.php`).
- UI/theme: Tailwind 3 + daisyUI, light theme only (`tailwind.config.js`, `<html data-theme="light">`).
- Formatting: use Laravel Pint: `./vendor/bin/pint`.

## Testing Guidelines

- Framework: PHPUnit via `php artisan test`.
- Location/naming: put tests in `tests/Unit` or `tests/Feature` and name `*Test.php`.
- Tests run with SQLite in-memory by default (see `phpunit.xml`).

## Commit & Pull Request Guidelines

- Commit messages: follow existing history style (imperative summaries like “Add …”, “Enhance …”, “Refactor …”).
- PRs: include a short summary, reproduction/verification steps, and screenshots for UI changes; note any migrations or config changes.

## Security & Configuration Tips

- Do not commit secrets from `.env`; update `.env.example` when adding new required variables.
- User uploads rely on `php artisan storage:link`; verify `public/storage` works before shipping.
