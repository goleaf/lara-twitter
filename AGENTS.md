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

- Keep Livewire components lean; extract query helpers into models/services.
- Use Volt pages for page-level UI; keep reusable UI in Blade or Livewire components.
- Validate via FormRequests when possible; otherwise keep Livewire rules centralized and named.
- Use pagination for feeds and large lists; avoid unbounded collections.
- Avoid heavy `mount()` work; defer with lazy properties or dispatch a job.
- Use `wire:key` for dynamic lists to reduce DOM churn.

## Build, Test, and Development Commands

- `composer setup`: install deps, create `.env`, migrate/seed, link storage, install/build frontend.
- `composer dev`: runs `php artisan serve`, queue listener, log tailing (Pail), and `npm run dev` via `concurrently`.
- `composer test`: clears config cache then runs `php artisan test`.
- Frontend only: `npm run dev` (HMR), `npm run build` (production build).

## Quick Optimization Checklist

- Add or adjust indexes with any new `where` + `order by` patterns.
- Cache expensive aggregates with viewer-aware keys and short TTLs.
- Keep Livewire payloads and API responses small; paginate large lists.
- Offload slow work to queues; keep jobs idempotent and sized by ids.
- Re-measure p95 latency and query counts after changes.

## Coding Style & Naming Conventions

- Indentation: 4 spaces (see `.editorconfig`); keep files LF-terminated.
- PHP: follow Laravel conventions; classes `PascalCase`, methods/vars `camelCase`.
- Blade views: keep templates in `resources/views/` and name files descriptively (e.g., `post-card.blade.php`).
- UI/theme: Tailwind 3 + daisyUI, light theme only (`tailwind.config.js`, `<html data-theme="light">`).
- Formatting: use Laravel Pint: `./vendor/bin/pint`.
- Prefer `FormRequest` validation, Policies for authorization, and `Service` classes for complex workflows.
- Keep controllers/Livewire actions thin; push query and domain logic into models/services.

## Performance & Data Access

- Prefer short-lived caching for expensive aggregates; include viewer id + inputs; TTL <= 2 minutes.
- Version/expire cache keys on writes that change feed visibility; use tags if available.
- Prevent stampedes with `Cache::lock()` or `Cache::add()`.
- Avoid `rememberForever` for personalized data; always use bounded TTLs.
- Cache ids or slim DTOs and rehydrate models; avoid caching large arrays or rendered HTML.
- Memoize per-request derived sets; use `User::mutedUserIds()` and `User::activeNotificationMutedTerms()`.
- Add indexes with new query patterns; use composite indexes for `where` + `order by`.
- Add indexes for `reply_to_id`, `is_reply_like`, date filters, and mute filters (`user_id`, mute flag, `expires_at`).
- Avoid eager-loading/counts when `exists()`/`max()` suffice; prefer `withExists()` over `withCount()`.
- Limit columns with `select()`; avoid `select *` in hot paths; use `pluck()` for id lists; use `->value()`/`->exists()` over `->get()`.
- Limit relation columns with `with(['relation:id,field'])` to reduce hydration cost.
- Avoid N+1; use targeted `with()`/`withCount()` only when needed.
- Prefer cursor or simple pagination for large feeds; use `cursorPaginate()` for infinite scroll; avoid unbounded offset scans.
- Use `->cursor()` for large exports to avoid memory spikes.
- Use `->toBase()` when you do not need model hydration.
- Prefer range queries (`>=`/`<`) over `whereDate()` to keep index usage.
- Chunk large `whereIn()` lists to avoid oversized SQL and poor query plans.
- Avoid leading-wildcard `LIKE` queries on large tables; use full-text where appropriate.
- Avoid `orWhere` chains without indexes; prefer unioned queries or search tables for complex filters.
- Prefer `whereExists` subqueries when you only need existence checks across large relations.
- Wrap multi-step writes in `DB::transaction()`; use `->lockForUpdate()` for concurrent writes.
- Use unique indexes with `upsert()`/`updateOrCreate()` for idempotent writes.
- Use `increment()`/`decrement()` for counters to avoid race conditions.
- Use `chunkById()`/`lazyById()` for batch jobs and backfills.
- Use `->latest('created_at')` only when backed by an index on `created_at`.

## Queues & Jobs

- Jobs must be idempotent; avoid duplicated side effects on retries.
- Pass ids, not full models; keep job payloads small.
- Set `backoff()`/`retryUntil()` for reliability; limit `maxExceptions` where needed.
- Set job `timeout`/`tries` for long-running work; align with supervisor settings.
- Use `Bus::batch()` for fan-out work and `WithoutOverlapping` for per-user tasks.
- Use `dispatchAfterResponse()` for non-critical work; keep web requests fast.
- Use named queues to separate latency-sensitive jobs from heavy processing.
- Use `afterCommit()` when dispatching jobs triggered by DB writes.

## Frontend, HTTP, and Media Performance

- Keep Livewire payloads small; avoid large arrays of models in component state.
- Prefer server-rendered markup for initial load; use JS only where interactivity is required.
- Shape responses explicitly; avoid returning entire models when only a few fields are needed.
- Validate inputs early and return fast `422` for invalid requests.
- Use `paginate()`/`simplePaginate()` for endpoints that can grow unbounded.
- Cache static responses with long-lived `Cache-Control` headers; use `ETag`/`If-None-Match` when possible.
- Rate-limit expensive or write-heavy routes; keep limits in `RouteServiceProvider`.
- Defer non-critical images/assets and avoid inlining large base64 blobs.
- Keep JS bundles small; prefer Vite code-splitting and remove unused dependencies.
- Store image variants and serve the smallest needed size.
- Strip EXIF and optimize uploads; avoid serving uncompressed originals to feeds.
- Persist width/height metadata to prevent layout shift on render.
- Run `npm run build` before release to catch Vite/Tailwind regressions.

## Database & Migrations

- Keep migrations small and reversible; prefer additive changes over destructive ones.
- Add indexes in the same migration as new query patterns; verify with `EXPLAIN`.
- Use `unsignedBigInteger` + foreign keys when relationships are strict.
- Run large backfills via batch jobs; avoid long locks and pick maintenance-safe windows.

## Observability, Errors, and Budgets

- Log with structured context (user id, request id, action); avoid logging PII.
- Guard expensive debug logging behind environment checks.
- Prefer `->toSql()` and query logging in local only; use `DB::listen()` sparingly.
- Track p95 latency, error rate, queue depth, and slow queries; alert on regressions.
- Track cache hit rate for key feeds to validate caching impact.
- Enable slow query logging at the database layer for production diagnostics.
- Propagate a request id (`X-Request-Id`) across logs and external calls.
- Add health checks for DB, cache, and queue connectivity.
- Standardize error responses; avoid leaking stack traces to clients.
- Use `report()` for unexpected errors and add context for actionable alerts.
- Prefer timeouts on external calls; retry with backoff only for idempotent actions.
- Fail fast on missing dependencies (cache/queue) instead of hanging requests.
- Define p95 and query-count budgets for core pages; cap feed payload sizes.

## Scaling & Capacity

- Align web/queue concurrency with DB connection limits to avoid saturation.
- Use Redis for cache/queue/session in production; avoid DB-backed drivers under load.
- Rotate logs and prune old media to prevent disk pressure.
- Use a CDN for static assets and media to reduce origin load.
- Ensure the scheduler runs once per minute (cron or `php artisan schedule:work`).
- Route analytics/reporting queries to read replicas if available.
- Schedule `queue:prune-batches` and `queue:prune-failed` to keep tables small.

## Data Retention & Cleanup

- Use Laravel `prunable`/`prune` for old notifications, sessions, and tokens.
- Purge or archive soft-deleted content on a retention window.
- Keep analytics tables summarized or partitioned to avoid unbounded growth.

## Upgrade & Release Playbook

- Scope upgrades; document cache/migration impacts; update lockfiles; remove unused packages.
- Run `composer test` and `npm run build` for dependency changes.
- Use `composer install --no-dev --optimize-autoloader` for production builds.
- Capture baseline metrics (p95 latency, query count, queue depth) before changes; re-measure after.
- Prioritize hottest endpoints (feeds, notifications, media) and apply indexes/caching first.
- Revert or roll back if p95 or error rates regress.
- Run migrations in maintenance-safe windows; keep a rollback plan and backups for large changes.
- Use `--step` migrations when you need granular rollbacks.
- Use feature flags for risky changes; roll out gradually after validation.
- Clear stale caches when needed (`config:clear`, `route:clear`, `view:clear`).
- Use `config:cache`/`route:cache`/`view:cache` only when routes avoid closures and config is stable.
- Use `php artisan optimize`/`optimize:clear` intentionally; avoid stale caches in deploy scripts.
- Restart queue workers after deploys (`php artisan queue:restart`); verify failed jobs and queue health.
- Smoke-test auth, posting, feeds, and notifications after upgrades.
- Warm critical caches (feeds/trending) and verify key versioning.
- Verify `public/storage` symlink and asset build output in `public/build/`.
- Set `APP_ENV`, `APP_DEBUG`, and `LOG_LEVEL` explicitly in production.
- Confirm `storage` and `bootstrap/cache` are writable by the web user.
- Enable OPcache in production; restart PHP-FPM on deploy if timestamp validation is disabled.
- Recycle workers with `--max-jobs`/`--max-time` to avoid memory bloat.
- Use gzip/brotli at the web server layer for assets and JSON responses.
- Set `ASSET_URL` when serving assets via CDN to improve cache hits.

## Testing & CI

- Framework: PHPUnit via `php artisan test`.
- Location/naming: put tests in `tests/Unit` or `tests/Feature` and name `*Test.php`.
- Tests run with SQLite in-memory by default (see `phpunit.xml`).
- Use model factories for fixtures; prefer feature tests for Livewire/HTTP flows.
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
- Do not call `env()` outside config files; use `config()` in application code.
