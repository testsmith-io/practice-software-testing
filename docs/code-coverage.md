# Runtime Code Coverage (Manual Testing)

Measure which PHP (Laravel API) and TypeScript (Angular UI) code is executed
while you test the application **manually** — clicking through the UI or
calling the API directly.

> **This feature is completely optional.** It lives in a separate Docker
> Compose overlay (`docker-compose.coverage.yml`). The normal workflow
> (`docker compose up -d` / `./start-app.sh`) is unchanged and has **zero
> coverage overhead**: the coverage extensions in the dev API image (Xdebug,
> PCOV) are disabled by default and only get switched on by the overlay.

## Quick Start

```bash
# Start in coverage mode (instead of ./start-app.sh)
./start-app-coverage.sh

# Wait for the UI build to finish (a minute or two on first start)
docker compose logs -f angular-ui

# Seed the database as usual
docker compose exec laravel-api php artisan migrate:fresh --seed

# Test manually:
#   UI:  http://localhost:4200
#   API: http://localhost:8091

# Watch the live, auto-refreshing reports:
#   http://localhost:4200/__reports/
```

To return to the normal workflow, just start the app the usual way
(`./start-app.sh`); nothing coverage-related remains active.

## Reports

| Stack | Live report | Metrics | Export |
|-------|-------------|---------|--------|
| PHP | [localhost:4200/__reports/php/](http://localhost:4200/__reports/php/) | lines, **branches**, **paths**, methods | `coverage-output/php/report/clover.xml` |
| TypeScript | [localhost:4200/__reports/ui/](http://localhost:4200/__reports/ui/) | statements, branches, functions, lines | `coverage-output/ui/report/lcov.info` |

Both are annotated-source HTML reports: every file can be opened to see
exactly which lines (and branches) were hit. The PHP report additionally has
dedicated *Branches* and *Paths* views per file.

### Auto-refresh

Reports regenerate automatically while you test: the UI report whenever new
coverage arrives from the browser, the PHP report whenever new API requests
were recorded. Report pages served from `/__reports/` reload themselves (and
keep your scroll position) when their report has been regenerated.

The interval is configurable via the `COVERAGE_REPORT_INTERVAL` environment
variable (seconds, default `30`, `0` disables auto-refresh):

```bash
COVERAGE_REPORT_INTERVAL=10 ./start-app-coverage.sh
```

On-demand generation still works at any time: `./coverage-report.sh`. The
generated reports are also plain files under `coverage-output/` if you prefer
opening them directly.

## How It Works

**PHP** — the dev API image contains Xdebug (disabled by default,
`xdebug.mode=off`). The overlay enables coverage mode and hooks a collector
into every FPM request via `auto_prepend_file`; each request dumps
branch-level coverage of `app/` and `routes/` as JSON. The dumps are replayed
through [php-code-coverage](https://github.com/sebastianbergmann/php-code-coverage)
to produce the report. Files that no request ever loaded are included at 0%.

**TypeScript** — instead of `ng serve`, the UI container builds the app once
(`ng build -c development`), instruments every bundle containing `src/` code
with Istanbul, and serves the result statically. A snippet injected into
`index.html` ships `window.__coverage__` to a collector endpoint every 10
seconds and on page hide. Reports are remapped onto the original `.ts` sources
via source maps. Lazy-loaded chunks that were never visited are reported at 0%.

All moving parts live in `_docker/coverage/` — see the
[README](https://github.com/testsmith-io/practice-software-testing/blob/main/_docker/coverage/README.md)
there for implementation details.

## Fresh Measurement / Cleanup

- Restarting via `./start-app-coverage.sh` archives the previous UI session
  automatically; for PHP, delete `coverage-output/php/raw/` to start clean.
- All collected data and reports live in `coverage-output/` (git-ignored);
  deleting that directory removes every trace of a session.

## Caveats

- **Coverage mode is slower** — Xdebug records branch data on every API
  request, and the instrumented UI executes counting code everywhere. This is
  inherent to the level of detail and only affects coverage mode.
- **No UI live reload** in coverage mode: it serves a one-off build. Restart
  the `angular-ui` container to pick up code changes.
- The UI ships coverage every 10 seconds and on page hide; the last few
  seconds before force-closing a tab may be lost.
- Works for any sprint (`SPRINT` in `.env`).
