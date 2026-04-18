# Postcode / Address Lookup (sprint 5)

Sprint 5 includes a **postcode + house number → address autofill** feature on the
registration and checkout-address forms. It's wired to a pluggable backend
service so you can demonstrate the real-world pattern of "my app calls an
external address API" — and, more importantly, **swap that dependency for a
mock** during development, testing, and demos.

## Why this exists

In the real world, apps call third-party services for postcode lookup (PostNL,
Royal Mail, Smarty, etc.). Those calls are inconvenient for testing: they cost
money, need credentials, have rate limits, and flake occasionally. This feature
is a hands-on example of the three options you have when you hit that problem:

1. **Use a local fake** (what ships by default — deterministic Faker output,
   no network calls).
2. **Use a stub server** (point at a WireMock container and script the
   responses you want to test).
3. **Use the real service** (point at the vendor URL in production).

Switching between them is a single environment variable.

## How it's wired

### Endpoint

```
GET /postcode-lookup?country=NL&postcode=1234AB&house_number=42
```

Response:
```json
{
  "street": "Stationsweg 42",
  "city": "Utrecht",
  "state": "Utrecht",
  "country": "NL",
  "postcode": "1234AB"
}
```

Implemented by `App\Http\Controllers\PostcodeController` and dispatched to a
`PostcodeService` with a pluggable driver.

### Default driver — Faker

`driver=faker` (default) returns deterministic fake addresses generated with
[FakerPHP](https://fakerphp.github.io/). The inputs are hashed into a seed, so
the **same `country|postcode|house_number` always yields the same address** —
predictable enough for screenshots and tests. The country code is mapped to a
Faker locale (`NL → nl_NL`, `DE → de_DE`, `GB → en_GB`, …), so the strings look
right for the chosen country. Unknown country codes fall back to `en_US`.

### HTTP driver — external lookup

`driver=http` makes the service call `GET {POSTCODE_LOOKUP_URL}/lookup` with
the same query parameters. The upstream must return JSON in the same shape.
Failures are reported as `502 Bad Gateway` to the frontend.

## Where it shows up in the UI

- **Registration** (`/auth/register`): country is at the top, followed by
  postal code + house number. A hint line and a spinner make the lookup
  explicit. Street / city / state appear autofilled once the lookup resolves.
- **Checkout → address step** (`/checkout`): same pattern.

The lookup fires when **all three** of country, postal code, and house number
have a value, debounced at 300 ms on keystroke so the backend isn't hammered
while typing.

---

## Pointing at a mock endpoint

The Faker default is fine for "does the flow work?". When you want to
**script specific responses** (slow answers, 5xx errors, deterministic
payloads for particular postcodes) run any mock server you like (WireMock,
Mockoon, Prism, `json-server`, Hoverfly, MSW) and tell the app to use it.

There are two ways to do that, and you'd pick based on your situation.

### Option A — via the admin Settings page (local Docker builds only)

**Availability**: this option is deliberately hidden on the public
`practicesoftwaretesting.com` deployment. It appears only when you run the
app from the Docker container locally, i.e. when the frontend build was
compiled with `environment.production = false`. Four independent checks
enforce this:

| Layer | Check |
|---|---|
| Settings template (`.html`) | `@if (showPostcodeLookupSettings)` — field not rendered |
| Settings component (`.ts`)  | `showPostcodeLookupSettings = !environment.production` — won't write localStorage |
| `PostcodeService` (frontend) | Only reads localStorage + sets the header when `!environment.production` |
| `PostcodeController` (backend) | Only honors the `X-Postcode-Lookup-Url` header when `App::environment() !== 'production'` |

This defense-in-depth is so that even if someone manually sends the header
against the public site, the backend ignores it — the feature is genuinely
local-only and not a latent SSRF vector.

#### Using it

1. Start the stack locally with `docker compose up` (any of the checked-in
   compose files works — the dev image has `environment.production = false`
   baked in).
2. Sign in as admin (`admin@practicesoftwaretesting.com` / `welcome01`), open
   **Admin → Settings**.
3. Paste your mock URL (e.g. `http://host.docker.internal:8080`) and save.
4. No restart required. The registration and checkout forms will now hit
   your mock on the next lookup.

Clear the field (or use the "Clear storage" button) to fall back to the
default faker-backed data.

How it works under the hood: the URL is stored in `localStorage` under the
key `POSTCODE_LOOKUP_URL`. On every lookup request, the frontend adds an
`X-Postcode-Lookup-Url: <your URL>` header. The backend validates and
uses it if we're not running in production.

### Option B — via `docker compose` env vars

If you prefer declarative/repo-committed configuration, set these three
variables on the `laravel-api` service:

| Variable | Default | Meaning |
|---|---|---|
| `POSTCODE_LOOKUP_DRIVER`  | `faker` | `faker` (built-in fake data, no external call) or `http` |
| `POSTCODE_LOOKUP_URL`     | *empty* | Base URL of your mock server (used when `driver=http`). |
| `POSTCODE_LOOKUP_TIMEOUT` | `5`     | HTTP timeout in seconds. Also applies to the Option A override. |

None of these need to be set for the default local-Docker flow: the `faker`
driver works out of the box and makes no network calls.

Example compose:

```yaml
services:
  laravel-api:
    image: testsmith/practice-software-testing-sprint5-api
    environment:
      - "PHP_OPCACHE_VALIDATE_TIMESTAMPS=1"
      - "DB_PORT=3306"
      - "DB_HOST=mariadb"
      - DISABLE_LOGGING=${DISABLE_LOGGING}
      # Postcode lookup: point at any mock server
      - POSTCODE_LOOKUP_DRIVER=http
      - POSTCODE_LOOKUP_URL=${POSTCODE_LOOKUP_URL:-http://host.docker.internal:8080}
      - POSTCODE_LOOKUP_TIMEOUT=5
```

Two common URL shapes:

- **Mock running on your host** (e.g. WireMock standalone on `localhost:8080`):
  `http://host.docker.internal:8080`. Works out of the box on Docker Desktop
  for Mac/Windows. On Linux add
  `extra_hosts: ["host.docker.internal:host-gateway"]`.
- **Mock running as another compose service**: `http://<service-name>:<port>`.
  Use the service name as hostname.

Bring the stack up or restart `laravel-api`. Every request to
`/postcode-lookup` now hits your mock.

### Precedence

When a lookup request comes in, the backend resolves the driver in this order:

1. `X-Postcode-Lookup-Url` header (admin UI override). Skipped on production.
2. `POSTCODE_LOOKUP_DRIVER` + `POSTCODE_LOOKUP_URL` env vars.
3. The default `faker` driver.

### The contract your mock needs to honor

```
GET {base}/lookup?country=NL&postcode=1234AB&house_number=42

200 OK
Content-Type: application/json

{
  "street":   "Stationsweg 42",
  "city":     "Utrecht",
  "state":    "Utrecht",
  "country":  "NL",
  "postcode": "1234AB"
}
```

Missing keys default to empty strings on the frontend side. Any non-2xx
response from the mock surfaces as `502 Bad Gateway` to the browser — handy
for exercising failure-path UX.

### Flipping back to the default

Remove the `POSTCODE_LOOKUP_*` env lines (or set `POSTCODE_LOOKUP_DRIVER=faker`)
and restart `laravel-api`. Zero rebuild required.

---

## When to reach for what

| Need | Pick |
|---|---|
| Feature tests, just need *something* plausible | `driver=faker` (default) |
| Reproduce a specific upstream response in a test | WireMock stub via `driver=http` |
| Hit the vendor in staging/production | `driver=http` pointed at the real URL |
| In-process assertion that the outbound call was made | Laravel `Http::fake()` in a feature test — *not* this feature's drivers |

The feature also makes a good teaching example for comparing **service
virtualisation** (WireMock — separate process, frontend-visible) with
**in-process fakes** (`Http::fake()` — Laravel-only, synchronous).
