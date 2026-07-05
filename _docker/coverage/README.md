# Runtime code coverage for manual testing

Measures which PHP (Laravel API) and TypeScript (Angular UI) code is executed
while you test the application **manually** in the browser / via the API.

Coverage mode is fully optional: it lives in the `docker-compose.coverage.yml`
overlay. The normal `docker compose up -d` workflow is unchanged (the only
permanent change is that the dev API image contains the PCOV extension,
disabled by default).

## Usage

```bash
./start-app-coverage.sh          # instead of ./start-app.sh (uses SPRINT from .env)
# wait for the UI build: docker compose logs -f angular-ui
# ... test manually at http://localhost:4200 (UI) / http://localhost:8091 (API) ...
# live, auto-refreshing reports: http://localhost:4200/__reports/
./coverage-report.sh             # optional: generate both reports on demand
```

Reports:

| Stack | Report | Raw data |
|-------|--------|----------|
| PHP   | `coverage-output/php/report/index.html` (+ `clover.xml`) | `coverage-output/php/raw/*.json` (one per request) |
| UI    | `coverage-output/ui/report/index.html` (+ `lcov.info`)   | `coverage-output/ui/raw/*.json` (one per page load) |

Both are annotated-source HTML reports. The PHP one (php-code-coverage) shows
**line, branch and path coverage** — every file has a "Lines" view plus
dedicated "Branches" and "Paths" views. The UI one (Istanbul) shows
statements, branches, functions and lines.

You can re-run `./coverage-report.sh` at any time during the session; it
merges everything collected so far. To start a fresh measurement, delete the
`raw` directories (or just restart with `./start-app-coverage.sh` — the UI
side archives the previous session automatically).

## How it works

**PHP** — the dev image contains Xdebug (and PCOV), both disabled by default.
The overlay mounts `php/coverage.ini` which sets `xdebug.mode=coverage` and
registers `php/collect.php` as `auto_prepend_file`: every FPM request records
branch-level coverage of `app/` and `routes/` (an Xdebug filter keeps the
framework out) and dumps it as JSON. `php/report.php` replays the dumps into
[php-code-coverage](https://github.com/sebastianbergmann/php-code-coverage)
and renders the full HTML report (annotated source, line + branch + path
coverage) plus Clover XML. Files that no request ever loaded are included at
0%, so the totals are honest. The report tooling is installed into
`php/tools/vendor` on first use of `coverage-report.sh` (isolated from the
application's own vendor directory).

**UI** — `ng serve` is replaced by `ui/serve-instrumented.sh`:
`ng build -c development` (source maps on), then every bundle that contains
`src/` code is instrumented with Istanbul (`ui/instrument.js`) and served
statically with an SPA fallback (`ui/server.js`). A snippet injected into
`index.html` posts `window.__coverage__` to the server every 10 s and on page
hide. `ui/report.js` merges the snapshots, remaps them onto the original
TypeScript sources through the embedded source maps, and writes HTML + lcov
(filtered to `src/**`). A zero-hit baseline of all instrumented bundles is
included, so lazy-loaded chunks you never visited still appear as 0%.

## Caveats

- **Auto-refresh**: while the stack runs, the reports regenerate whenever new
  coverage arrives (every `COVERAGE_REPORT_INTERVAL` seconds, default 30,
  0 disables it — the UI report via `ui/server.js`, the PHP report via the
  `coverage-php-watcher` service running `php/watch.sh`). Pages under
  `http://localhost:4200/__reports/` reload themselves when their report
  changed; reports opened directly from `coverage-output/` need a manual
  refresh after regeneration.
- **UI has no live reload in coverage mode** — it serves a one-off build.
  Restart the `angular-ui` container to pick up code changes.
- **API requests are noticeably slower** in coverage mode (Xdebug records
  branch/path data on every request) and the instrumented UI is slower than a
  normal build; both are expected and only affect coverage mode.
- The UI ships `window.__coverage__` to the collector every 10 seconds and on
  page hide, so the last few seconds before closing a tab may be lost.
- On Linux hosts, the containers write to the bind-mounted `coverage-output/`
  as their own users; if you get permission errors run
  `chmod -R 777 coverage-output`.
- Works for any sprint (`SPRINT` in `.env`), but the intentionally buggy
  variants are of course also measured as-is.
