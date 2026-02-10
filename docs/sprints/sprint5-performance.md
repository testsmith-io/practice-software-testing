# Sprint 5 (performance)

A variant of Sprint 5 with **performance degradation middleware** applied to key endpoints. Designed for load testing, resilience testing, and performance engineering practice.

## Purpose

- Practice performance and load testing
- Observe application behavior under stress
- Test monitoring and alerting setups
- Understand degradation strategies

## How It Works

Selected API endpoints include a `performance.degrade` middleware that simulates realistic performance degradation under load. As request volume increases, response times gradually increase based on configurable strategies.

### Middleware Parameters

| Parameter         | Description                                             |
|-------------------|---------------------------------------------------------|
| `threshold`       | Number of requests before degradation begins            |
| `window`          | Time window in seconds for counting requests            |
| `max_delay`       | Maximum added delay in milliseconds                     |
| `strategy`        | Degradation curve: `exponential`, `stepped`, or `linear`|
| `scope`           | Scope of rate tracking (e.g., `ip`)                     |
| `degradation_type`| Type of degradation (e.g., `blocking`)                  |

## Affected Endpoints

| Endpoint                         | Threshold | Window | Max Delay | Strategy    |
|----------------------------------|-----------|--------|-----------|-------------|
| `GET /brands`                    | 20        | 300s   | 3000ms    | exponential |
| `GET /categories/tree`           | 10        | 60s    | 2000ms    | stepped     |
| `GET /categories/search`         | 10        | 60s    | 2000ms    | stepped     |
| `GET /invoices/{id}`             | 50        | 300s   | 2000ms    | stepped     |
| `GET /products`                  | 50        | 60s    | 1000ms    | linear      |
| `GET /reports/total-sales-of-years` | 20     | 300s   | 3000ms    | exponential |
| `POST /users/login`              | 10        | 60s    | 2000ms    | stepped     |
| `POST /users/register`           | 30        | 300s   | 1000ms    | linear      |

## Degradation Strategies

- **Exponential** - Delay increases exponentially as requests approach the threshold. Simulates cascading slowdowns.
- **Stepped** - Delay increases in discrete steps. Simulates tiered resource contention.
- **Linear** - Delay increases proportionally with request count. Simulates gradual resource exhaustion.

## Implementation Details

Performance degradation is implemented in both the **API** (Laravel middleware) and the **frontend** (Angular directive).

---

### API – Performance Degradation Middleware

#### Source files

| File | Description |
|------|-------------|
| `API/app/Http/Middleware/PerformanceDegradationMiddleware.php` | Middleware class — tracks request counts per IP via cache, calculates delay based on strategy, applies `usleep()` blocking delays, and throws simulated 504 errors |
| `API/app/Http/Kernel.php:38` | Registers the middleware alias `performance.degrade` |
| `API/routes/api.php` | Applies the middleware to specific routes (see table below) |

#### Route-level middleware registration

| Route file location | Endpoint | Middleware parameters |
|----------------------|----------|-----------------------|
| `routes/api.php:48` | `GET /brands` | `threshold:20,window:300,max_delay:3000,strategy:exponential` |
| `routes/api.php:68` | `GET /categories/tree` | `threshold:10,window:60,max_delay:2000,strategy:stepped` |
| `routes/api.php:71` | `GET /categories/search` | `threshold:10,window:60,max_delay:2000,strategy:stepped,degradation_type:blocking` |
| `routes/api.php:103` | `GET /invoices/{id}` | `threshold:50,window:300,max_delay:2000,strategy:stepped,degradation_type:blocking` |
| `routes/api.php:118` | `GET /products` | `threshold:50,window:60,max_delay:1000,strategy:linear,degradation_type:blocking` |
| `routes/api.php:130` | `GET /reports/total-sales-of-years` | `threshold:20,window:300,max_delay:3000,strategy:exponential,degradation_type:blocking` |
| `routes/api.php:146` | `POST /users/login` | `threshold:10,window:60,max_delay:2000,strategy:stepped,degradation_type:blocking` |
| `routes/api.php:150` | `POST /users/register` | `threshold:30,window:300,max_delay:1000,strategy:linear,degradation_type:blocking` |

---

### Frontend – Render Delay Directive

#### Source file

| File | Description |
|------|-------------|
| `UI/src/app/render-delay-directive.directive.ts` | Angular directive that introduces artificial rendering delays — sets element opacity to 0.3, disables buttons and links, then re-enables after a random delay |

#### Behavior

- On render, the host element is set to **30% opacity**
- All **buttons** inside the element are **disabled**
- All **anchor tags** have pointer events disabled and opacity reduced to 50%
- After a random delay (within the configured range), the element is restored to normal
- Default delay range: **500–2000ms**

#### Affected components

| Component | File | Delay range |
|-----------|------|-------------|
| Admin – Users list | `UI/src/app/admin/users-list/users-list.component.html:13` | 500–2000ms |
| Admin – Products list | `UI/src/app/admin/products-list/products-list.component.html:13` | 500–2000ms |
| Admin – Orders list | `UI/src/app/admin/orders-list/orders-list.component.html:12` | 500–2000ms |
| Admin – Brands list | `UI/src/app/admin/brands-list/brands-list.component.html:12` | 500–2000ms |
| Admin – Categories list | `UI/src/app/admin/categories-list/categories-list.component.html:12` | 500–2000ms |
| Admin – Messages list | `UI/src/app/admin/messages-list/messages-list.component.html:4` | 500–2000ms |
| Admin – Avg sales/month | `UI/src/app/admin/reports/average-sales-month/average-sales-month.component.html:13` | 1500–3500ms |
| Admin – Avg sales/week | `UI/src/app/admin/reports/average-sales-week/average-sales-week.component.html:13` | 1500–3500ms |
| Admin – Statistics | `UI/src/app/admin/reports/statistics/statistics.component.html:7,31,57,81` | 500–2000ms |
| Account – Profile | `UI/src/app/account/profile/profile.component.html:1` | 1000–2000ms |
| Account – Messages | `UI/src/app/account/messages/messages.component.html:8` | 500–2500ms |
| Account – Invoices | `UI/src/app/account/invoices/invoices.component.html:4` | 500–2000ms |
| Account – Favorites | `UI/src/app/account/favorites/favorites.component.html:9` | 500–2000ms |
| Account – Message detail | `UI/src/app/account/messages/message-detail/message-detail.component.html:1` | 750–2500ms |
| Account – Invoice detail | `UI/src/app/account/invoices/details/details.component.html:7` | 500–2000ms |

---

## List of Known Behaviors

These are intentional performance degradation characteristics that testers should be aware of.

### API Behaviors

| # | Area | Behavior | Location |
|---|------|----------|----------|
| 1 | All degraded endpoints | **Simulated 504 Gateway Timeout** — When delay reaches `max_delay`, 20% of requests return a 504 instead of a normal response | `PerformanceDegradationMiddleware.php:128-138` |
| 2 | All degraded endpoints | **Blocking worker threads** — Delays use `usleep()` which blocks the PHP worker; sustained load can exhaust the worker pool | `PerformanceDegradationMiddleware.php:125` |
| 3 | All degraded endpoints | **Per-IP counter reset** — Switching client IP resets the request counter, bypassing the threshold | `PerformanceDegradationMiddleware.php:89-90` |
| 4 | All degraded endpoints | **Cache-dependent counters** — Counters stored in Laravel cache; restarting the cache driver resets all counters | `PerformanceDegradationMiddleware.php:30-33` |
| 5 | Exponential strategy | **Narrow degradation band** — Uses `100 * 1.5^n`, so delay jumps from trivial to max within a narrow range of excess requests | `PerformanceDegradationMiddleware.php:158-160` |
| 6 | Stepped strategy | **Fixed step thresholds** — Delays jump at fixed excess-request counts (5, 10, 20, 50, 100) regardless of the endpoint's configured threshold | `PerformanceDegradationMiddleware.php:164-171` |
| 7 | All degraded endpoints | **Window TTL reset** — Each `Cache::put()` resets the TTL to the full window duration, so steady traffic never lets the counter expire | `PerformanceDegradationMiddleware.php:33` |

### Frontend Behaviors

| # | Area | Behavior | Location |
|---|------|----------|----------|
| 8 | Admin list pages | **Render delay 500–2000ms** — Page content fades to 30% opacity and buttons/links are disabled for a random period | `render-delay-directive.directive.ts` |
| 9 | Admin report pages | **Extended render delay 1500–3500ms** — Report charts experience longer delays than list pages | `average-sales-month.component.html:13`, `average-sales-week.component.html:13` |
| 10 | Account pages | **Render delay 500–2500ms** — Profile, messages, invoices, and favorites pages experience rendering delays | `render-delay-directive.directive.ts` |
| 11 | All affected pages | **Buttons disabled during delay** — All buttons inside the directive host are set to `disabled="true"` during the delay period | `render-delay-directive.directive.ts:25` |
| 12 | All affected pages | **Links blocked during delay** — Anchor tags have `pointerEvents` set to `none` and click events are blocked during the delay | `render-delay-directive.directive.ts:30-40` |
| 13 | All affected pages | **No loading indicator** — Elements fade to 30% opacity but no spinner or skeleton screen is shown; users see a dimmed, unresponsive page | `render-delay-directive.directive.ts:16` |
| 14 | All affected pages | **No error handling for 504** — The frontend has no specific handling for the simulated 504 timeouts thrown by the API middleware | HTTP interceptors in `_helpers/` |

## All Other Features

This version includes all Sprint 5 features (cart, social login, 2FA, admin dashboard, etc.) alongside the performance degradation.
