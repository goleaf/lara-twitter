# Repository Guidelines

## Project Structure & Module Organization

- `app/`: PHP application code (Laravel 12).
  - `app/Livewire/`: Livewire v3 page/components (timeline, profile, posts).
  - `app/Filament/`: Filament admin panel resources.
  - `app/Models/`, `app/Services/`, `app/Observers/`: domain logic and model lifecycle.
  - `app/Http/Requests/`: FormRequest validation (prefer rules here, not inline).
- `routes/`: route definitions (`routes/web.php`, `routes/auth.php` using Livewire Volt pages).
- `resources/`: frontend source.
  - `resources/views/`: Blade templates + Livewire/Volt views (see `resources/views/livewire/pages/auth/`).
  - Layout: use `resources/views/layouts/app.blade.php` as the single shared layout.
  - `resources/css/`, `resources/js/`: Vite entrypoints (`resources/css/app.css`, `resources/js/app.js`).
- `database/`: migrations, factories, seeders, and local SQLite file (`database/database.sqlite`).
- `tests/`: PHPUnit tests (`tests/Feature/`, `tests/Unit/`).
- `public/`: web root; built assets land in `public/build/` (Vite).

## Livewire & Volt Conventions

- Keep Livewire components lean: prefer computed properties and extracted query helpers in models/services.
- Use Volt pages for page-level UI; keep reusable UI in Blade components or Livewire components.
- Validate via FormRequests when possible; otherwise keep Livewire rules centralized and named.
- Use pagination for feeds and large lists; avoid loading unbounded collections.
- Avoid heavy `mount()` work; defer with lazy properties or dispatch a job when appropriate.
- Use `wire:key` for dynamic lists to reduce DOM churn.

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
- Prefer `FormRequest` validation, Policies for authorization, and `Service` classes for complex workflows.
- Keep controllers/Livewire actions thin; push query and domain logic into models/services.

## Performance, Caching, and Indexing

- Prefer short-lived caching for expensive aggregate queries (trending, discovery) and keep cache keys viewer-aware.
- Avoid eager-loading/counts when doing `exists()`/`max()` checks; use lean queries for activity probes.
- Add indexes alongside new query patterns (especially for `reply_to_id`, `is_reply_like`, and date filtering).
- When caching personalized results, include viewer id + key inputs, and keep TTLs low (<= 2 minutes).
- Memoize per-request derived sets (muted terms/excluded user ids) on the model to reduce duplicate queries.
- Add composite indexes that match `where` + `order by` patterns for feed, reply, and analytics queries.
- For mute filtering, prefer composite indexes covering `user_id`, the mute flag, and `expires_at`.
- Prefer cached helpers like `User::mutedUserIds()` and `User::activeNotificationMutedTerms()` for notification filtering.
- Use `select()` to limit columns on heavy queries; avoid `->get()` when `->exists()` or `->value()` is enough.
- Queue slow work (notifications, media processing) and keep web requests fast.
- Prefer cursor or simple pagination for large feeds; avoid unbounded `offset` scans.
- Avoid N+1 queries; use targeted `with()` and `withCount()` only when required.
- Use `dispatchAfterResponse()` for non-critical work that can happen after the request ends.
- Wrap multi-step writes in `DB::transaction()` to keep data consistent.
- Prefer `withExists()` over `withCount()` for boolean checks.
- Use `pluck()` for id lists and `chunkById()`/`lazyById()` for batch jobs.
- For cache invalidation, expire or version keys on writes that change feed visibility.
- Use `->latest('created_at')` only when backed by an index on `created_at`.
- Prefer `->cursor()` for large exports to avoid memory spikes.

## Caching Strategy

- Prefer caching ids or slim DTOs; rehydrate models in-app to avoid stale relations.
- Use versioned keys or tags (if supported) for safe, targeted invalidation.
- Prevent stampedes with `Cache::lock()` or `Cache::add()` around expensive builds.
- Keep cached payloads small; avoid storing large arrays or rendered HTML.

## Queue & Jobs

- Jobs must be idempotent; avoid duplicated side effects on retries.
- Pass ids, not full models; keep job payloads small.
- Set `backoff()`/`retryUntil()` for reliability; limit `maxExceptions` where needed.
- Use `Bus::batch()` for fan-out work and `WithoutOverlapping` for per-user tasks.

## Concurrency & Consistency

- Add unique indexes that match logical uniqueness; do not rely on app-only checks.
- Use `->lockForUpdate()` when multiple writes depend on the same row.
- Prefer `increment()`/`decrement()` for counters to avoid race conditions.
- Use `upsert()` or `updateOrCreate()` with unique constraints for idempotent writes.

## Frontend Performance

- Keep Livewire payloads small; avoid large arrays of models in component state.
- Prefer server-rendered markup for initial load; use JS only where interactivity is required.
- Defer non-critical images/assets and avoid inlining large base64 blobs.
- Run `npm run build` before release to catch Vite/Tailwind regressions.

## HTTP & API Performance

- Prefer `paginate()`/`simplePaginate()` for endpoints that can grow unbounded.
- Shape responses explicitly; avoid returning entire models when only a few fields are needed.
- Cache static responses (avatars, media, assets) with long-lived `Cache-Control` headers.
- Rate-limit expensive or write-heavy routes; keep limits in `RouteServiceProvider`.

## Database & Migrations

- Keep migrations small and reversible; prefer additive changes over destructive ones.
- Add indexes in the same migration as new query patterns; verify with `EXPLAIN` on hot paths.
- Use `unsignedBigInteger` + foreign keys when relationships are strict; avoid silent orphaning.
- For large backfills, use batch jobs or `chunkById()` to avoid timeouts.

## Observability & Debugging

- Log with structured context (user id, request id, action) to speed up incident triage.
- Guard expensive debug logging behind environment checks.
- Prefer `->toSql()` and query logging in local only; never in production.
- Use `DB::listen()` sparingly to diagnose N+1 or slow queries.
- Track p95 latency, error rate, queue depth, and slow queries; alert on regressions.
- Add health checks for DB, cache, and queue connectivity.

## Performance Budgets

- Define p95 targets per endpoint and measure after changes.
- Cap feed payload sizes; avoid loading more than needed for above-the-fold UI.
- Track query counts for core pages and keep them stable across releases.

## Upgrade & Maintenance

- Keep upgrade work scoped; document any cache/migration impacts in PR notes.
- For dependency upgrades, update lockfiles and run `composer test` and `npm run build` to verify.
- Verify queued jobs still run after upgrades (queue connection, failed jobs table).
- Re-run `php artisan config:clear` if config changes are not reflected in runtime.
- Confirm `php artisan route:clear` and `php artisan view:clear` for stale route/view issues.
- Restart queue workers after deploys when code changes touch jobs.
- For schema changes, run migrations in maintenance-safe windows; avoid long locks.
- After upgrades, smoke-test auth, posting, feeds, and notifications.

## Dependency Hygiene

- Keep `composer.lock` and `package-lock.json` updated alongside dependency changes.
- Remove unused packages to reduce install time and attack surface.
- Prefer `composer install --no-dev --optimize-autoloader` for production builds.
- Run `npm prune` when removing frontend dependencies.

## Deployment & Ops

- Use `php artisan optimize`/`optimize:clear` intentionally; avoid stale caches in deploy scripts.
- Keep queue workers supervised and restart on deploy (Supervisor/Horizon).
- Prefer Redis for cache/queue in production; avoid database drivers for high volume.
- Set `APP_ENV`, `APP_DEBUG`, and `LOG_LEVEL` explicitly in production.
- Confirm `storage` and `bootstrap/cache` are writable by the web user.

## Release Safety & Rollbacks

- Keep a rollback plan for schema changes; avoid destructive migrations without a restore path.
- Use feature flags for risky changes and roll out gradually.
- Back up the database before large migrations and confirm restore procedure.
- Use `php artisan down`/`up` for high-risk deploys that can tolerate brief downtime.

## Runtime Tuning

- Enable OPcache in production; restart PHP-FPM on deploy if timestamp validation is disabled.
- Recycle workers with `--max-jobs`/`--max-time` to avoid memory bloat.
- Use gzip/brotli at the web server layer for assets and JSON responses.
- Set `ASSET_URL` when serving assets via CDN to improve cache hits.

## Profiling & Load

- Use Laravel Telescope/Clockwork locally to find slow queries and N+1 issues.
- Benchmark feed endpoints after schema or cache changes; keep a baseline.
- Prefer staged rollouts for performance-sensitive changes.

## Release Checklist

- Run `composer test` and `npm run build` before release.
- Verify `php artisan migrate --force` completes and queues are healthy.
- Warm critical caches if used (feeds/trending) and verify key versioning.
- Check `public/storage` symlink and asset build output in `public/build/`.

## Testing Guidelines

- Framework: PHPUnit via `php artisan test`.
- Location/naming: put tests in `tests/Unit` or `tests/Feature` and name `*Test.php`.
- Tests run with SQLite in-memory by default (see `phpunit.xml`).
- Use model factories for fixtures; prefer feature tests for Livewire/HTTP flows.

## CI & Quality Gates

- Run `./vendor/bin/pint` and `composer test` in CI; fail the build on errors.
- Run `npm run build` in CI for frontend regressions.
- Require migrations and new queries to include index considerations in PR notes.

## Commit & Pull Request Guidelines

- Commit messages: follow existing history style (imperative summaries like “Add …”, “Enhance …”, “Refactor …”).
- PRs: include a short summary, reproduction/verification steps, and screenshots for UI changes; note any migrations or config changes.

## Security & Configuration Tips

- Do not commit secrets from `.env`; update `.env.example` when adding new required variables.
- User uploads rely on `php artisan storage:link`; verify `public/storage` works before shipping.
- Validate and authorize all user-facing actions; prefer Policies/Gates over inline checks.
- Keep `APP_DEBUG=false` in production and use `php artisan config:cache` for faster bootstrap.
- Avoid logging PII; mask or omit sensitive fields in logs and errors.
