# Angular Deep Dive — ToolShop Codebase & User Story Mapping

**Purpose of this doc**: get anyone — regardless of prior Angular experience — from zero to "I can confidently implement or change a feature" in this codebase. Part 1 explains the concepts using the actual patterns in this repo. Part 2 walks through every backlog user story and tells you exactly what to touch and how.

---

## Table of Contents

- [What Is Angular? A Restaurant Analogy](#what-is-angular-a-restaurant-analogy)
- [Part 1: How Angular Works in This Project](#part-1-how-angular-works-in-this-project)
  - [The Mental Model](#the-mental-model)
  - [What This Project Actually Uses](#what-this-project-actually-uses)
  - [The Actual File Tree](#the-actual-file-tree)
  - [How Data Flows (Payment Example)](#how-data-flows-payment-example)
  - [How Routing Works](#how-routing-works)
- [Part 2: How Angular Maps to Each User Story](#part-2-how-angular-maps-to-each-user-story)
  - [US1003 - How to Angular (Onboarding)](#us1003---how-to-angular-onboarding)
  - [US1007 - New Logo](#us1007---new-logo)
  - [US1008 - Remove Rentals](#us1008---remove-rentals)
  - [US2300 - Czech Language Support](#us2300---czech-language-support)
  - [US2350 - Czech Product Content](#us2350---czech-product-content)
  - [US3100 - PayU Payment Integration](#us3100---payu-payment-integration)
  - [US4200 - Delivery Costs](#us4200---delivery-costs)
  - [US4350 - Version Number Display](#us4350---version-number-display)
  - [US4500 - Register with Google](#us4500---register-with-google)
  - [US4510 - Automated Regression Tests (Playwright)](#us4510---automated-regression-tests-playwright)
  - [US9100 — Mock PayU Payment Service](#us9100-mock-payu-payment-service-backend-but-angular-touches-it)
  - [US9200 - PayU TIP Testing UI](#us9200---payu-tip-testing-ui)
- [Summary: Dependency Map](#summary-dependency-map)

---

## What Is Angular? A Restaurant Analogy

Think of the ToolShop app as a restaurant.

- **A component is a station in the kitchen** (the grill station, the dessert station, the drinks station). Each station has three things bolted together: a **recipe card** (the TypeScript class — the logic and data), a **plating template** (the HTML template — how the dish looks when it goes out), and a **station-specific seasoning kit** (the CSS — styles scoped to that station only, so the dessert station's sugar doesn't end up on the grill's steak).

- **A module is a section of the restaurant** — "Checkout" is the front-of-house payment counter, "Products" is the dining room and menu browsing area, "Auth" is the coat-check/ID-verification desk. Each section bundles together the stations (components) it needs to do its job.

- **Routing is the host stand.** When a guest (the browser) walks in and says "table for checkout," the host (the router) doesn't build a new section on the spot — it walks the guest to the already-organized Checkout section.

- **Lazy loading means a section only opens once someone actually orders from it.** The dessert section doesn't heat its ovens until a guest asks for cake — Angular doesn't download and initialize the Checkout module's code until someone actually navigates to `/checkout`. Faster kitchen, faster app.

- **Services are the shared pantry / central prep kitchen.** Every station needs eggs, flour, or price lists — instead of each station keeping its own stash (and going stale or getting out of sync), they all pull from one shared pantry. `CartService`, `PaymentService`, and `ProductService` are that shared pantry for cart state, payment logic, and product data.

- **Dependency Injection is how a station requests pantry items** — a cook doesn't walk to the supplier's warehouse themselves; they say "send me the pantry" and it appears. That's what `private paymentService = inject(PaymentService)` means: "give me the shared pantry item called PaymentService," no manual setup required.

- **Reactive Forms are the order pad.** A guest's order isn't accepted until it's filled out correctly — no protein selected? The kitchen won't fire the ticket. `Validators.required` and friends are the rules the order pad enforces before a ticket can be submitted.

- **Transloco is the multilingual menu.** The same dish exists on the German menu, the French menu, and the Turkish menu — same underlying recipe, different printed text. `t('header.menu.home')` is "print whichever language's word for 'Home' matches the guest's menu."

- **Data binding is the ticket rail between the kitchen and the front desk.** The order flows from the front (template) to the kitchen (class) — `(click)="logout()"` — and status flows back out to the display — `{{ items }}`, `[disabled]="!valid"`. It's a two-way conveyor belt, not a one-time handoff.

- **`data-test` attributes are table numbers.** They don't affect the food or the guest experience at all — they exist purely so the health inspector (Playwright, the test runner) can walk in and reliably find "table 12" every single time, regardless of how the room gets rearranged.

Keep this picture in your head. Every section below is really just: *which station, which section, and which pantry item do I need to touch?*

---

## Part 1: How Angular Works in This Project

### The Mental Model

Angular is a **component-based framework**. Think of the UI as a tree of LEGO bricks (or, per the analogy above, a tree of kitchen stations). Each brick (component) has:
- A **TypeScript class** — holds data and logic
- An **HTML template** — the visual rendering
- **CSS styles** — scoped to this component only

The app boots from `main.ts` → `AppComponent` → the router picks a page → the matching component renders.

### What This Project Actually Uses

| Concept | What It Means Here | Where You'll See It |
|---|---|---|
| **Components** | Each UI piece is a class + template pair | `HeaderComponent`, `PaymentComponent`, `OverviewComponent` |
| **Modules** | Feature groups that bundle components | `CheckoutModule`, `ProductsModule`, `AuthModule` |
| **Routing** | URL → component mapping | `app-routing.module.ts` maps `/checkout` → `CheckoutModule` |
| **Lazy Loading** | Modules loaded only when needed | `loadChildren: () => import('./checkout/checkout.module')` |
| **Services** | Shared logic (API calls, state) | `PaymentService`, `CartService`, `ProductService` |
| **Dependency Injection** | Components request services via `inject()` | `private paymentService = inject(PaymentService)` |
| **Reactive Forms** | Form building with validation | `FormBuilder.group({...})`, `Validators.required` |
| **Transloco** | i18n translation system | `*transloco="let t"`, `t('header.menu.home')` |
| **Data binding** | Template ↔ class data flow | `{{ items }}`, `[disabled]="!valid"`, `(click)="logout()"` |
| **`data-test` attrs** | Test selectors (Playwright hooks) | `data-test="nav-sign-in"`, `data-test="payment-method"` |

### The Actual File Tree

```
sprint5/UI/src/app/
├── app.component.html          ← Root: header + <router-outlet> + footer
├── app-routing.module.ts       ← Top-level routes (lazy-loaded modules)
├── header/                     ← Nav bar, language switcher, cart badge
├── footer/                     ← Copyright line
├── products/                   ← Product overview, detail, category, rentals, comparison
├── checkout/                   ← 4-step wizard: Cart → Login → Address → Payment
├── auth/                       ← Login, Register, Forgot Password
├── account/                    ← User profile, invoices, favorites, messages
├── admin/                      ← Admin dashboard, CRUD for all entities
├── _services/                  ← 22 services (API calls, state)
├── _helpers/                   ← Validators, discount utilities
├── models/                     ← TypeScript interfaces (Product, User, etc.)
├── shared/                     ← Reusable components (password input, validators)
├── transloco-root.module.ts    ← Translation config (7 languages)
└── assets/i18n/                ← Translation JSON files (en, de, el, es, fr, nl, tr)
```

### How Data Flows (Payment Example)

```
User clicks "Confirm" in PaymentComponent
  → finishFunction() builds payload
    → checkPayment() calls PaymentService.validate()
      → HTTP POST to /payment/check
        → Response: { message: "..." } or { error: "..." }
          → Sets this.paymentMessage or this.paymentError
            → Template shows success/error alert via *ngIf
```

### How Routing Works

```
Browser hits /checkout
  → app-routing.module.ts matches 'checkout'
    → Lazy-loads CheckoutModule
      → checkout-routing.module.ts renders CheckoutComponent
        → CheckoutComponent template has <aw-wizard> with 4 steps
          → Each step is a child component (Cart, Login, Address, Payment)
```

---

## Part 2: How Angular Maps to Each User Story

### US1003 - How to Angular (Onboarding)

**What to learn**: The patterns already in the codebase.

| Pattern | Where | What to Study |
|---|---|---|
| Standalone-style imports | `header.component.ts` line 16-21 | Components declare their own imports (not in a module) |
| `inject()` pattern | Every service usage | `private x = inject(SomeService)` — replaces constructor injection |
| `@Input()` / `@Output()` | `payment.component.ts` line 39, `address.component.ts` line 31-32 | Parent passes data down, child emits events up |
| Transloco directive | `*transloco="let t"` in every template | How translations work |
| Reactive forms | `payment.component.ts` line 52-64 | `FormBuilder.group()` + `Validators` |
| `data-test` selectors | Every template | How Playwright targets elements |

**Exercise**: Modify a template text, add a `data-test` attribute, verify it works with Playwright.

---

### US1007 - New Logo

**Angular mechanism**: The logo is an inline SVG in `header.component.html` (lines 20-67).

**Implementation approach**:
1. Replace the SVG in `header.component.html` — it's a single `<svg>` block inside the `<a class="navbar-brand">` tag
2. For market-specific logos, use Transloco or a conditional:
   ```html
   <a class="navbar-brand" href="/">
     @if (activeLanguage === 'cs') {
       <img src="assets/logo-cz.png" alt="ToolShop CZ" />
     } @else {
       <svg ...> <!-- existing SVG --> </svg>
     }
   </a>
   ```
3. The `activeLanguage` property already exists in `header.component.ts` (line 30, set at line 57)

**Files to touch**: `header.component.html`, `header.component.ts` (if adding logic)

---

### US1008 - Remove Rentals

**Angular mechanism**: Rentals are a route + component inside `ProductsModule`.

**What exists**:
- Route: `products-routing.module.ts` line 16 → `{ path: 'rentals', component: RentalOverviewComponent }`
- Component: `products/rentals/overview/overview.component.ts` — fetches `productService.getProductRentals()`
- Nav link: `header.component.html` line 96 → `<a ... routerLink="/rentals">{{ t('header.menu.rentals') }}</a>`
- Translation key: `header.menu.rentals` in all 7 JSON files

**Implementation approach**:
1. **Delete** the nav link from `header.component.html` (line 96 — the `<li>` containing the rentals link)
2. **Remove** the route from `products-routing.module.ts` (line 16)
3. **Optionally delete** the `products/rentals/` directory entirely
4. **Remove** `getProductRentals()` from `product.service.ts`
5. **Remove** the `header.menu.rentals` key from all 7 translation files in `assets/i18n/`

**Testing impact**: Any Playwright test targeting `/rentals` or `data-test="nav-rentals"` will break — update or remove those tests.

---

### US2300 - Czech Language Support

**Angular mechanism**: Transloco i18n system.

**What exists**:
- `transloco-root.module.ts` line 9: `availableLangs = ['de', 'el', 'en', 'fr', 'es', 'nl', 'tr']` — **Czech ('cs') is missing**
- Translation files: `assets/i18n/{en,de,el,es,fr,nl,tr}.json` — **no `cs.json`**
- Language switcher: `header.component.html` lines 194-200 — lists all languages as `<a>` tags
- Language persistence: `localStorage.setItem('language', language)` in `header.component.ts` line 90
- Auto-detection: `transloco-root.module.ts` lines 11-30 — detects browser language

**Implementation approach**:
1. **Add `'cs'` to `availableLangs`** in `transloco-root.module.ts`
2. **Create `assets/i18n/cs.json`** — translate all keys from `en.json` (543 lines). Start with navigation, checkout, errors, validation messages (per ACC-03 through ACC-10)
3. **Add Czech to the language switcher** in `header.component.html`:
   ```html
   <li role="menuitem"><a class="dropdown-item" data-test="lang-cs" (click)="changeSiteLanguage('cs')">CS</a></li>
   ```
4. **Update the auto-detection** in `transloco-root.module.ts` — it will automatically pick up 'cs' from browser settings once it's in `availableLangs`
5. **Ensure Transloco re-renders** — `reRenderOnLangChange: true` is already set (line 45)

**Key challenge**: The `en.json` is 543 lines. You need to translate every key. Some keys use interpolation like `{invoice_number}` — these must be preserved in Czech translations.

**ACC mapping**:
| AC | How Angular Delivers It |
|---|---|
| ACC-01: Visible switcher | Add `<li>` in header template |
| ACC-02: Switch anytime | `changeSiteLanguage('cs')` already works for any language |
| ACC-03: Nav in Czech | All `t('header.menu.*')` keys in `cs.json` |
| ACC-04: UI elements in Czech | All `t('buttons.*')`, `t('pages.*')` keys in `cs.json` |
| ACC-05-06: Product titles/descs | Backend must return Czech content OR frontend translates static product data |
| ACC-07: Error messages in Czech | Validation error keys in `cs.json` |
| ACC-08: Form validation in Czech | Form error messages use `t('pages.checkout.payment.*')` |
| ACC-09: Language persists | `localStorage` already handles this |
| ACC-10: Checkout in Czech | Checkout component already uses `*transloco` |

---

### US2350 - Czech Product Content

**Angular mechanism**: Product data comes from the API. The frontend displays whatever the backend returns.

**Two approaches**:
1. **Backend-driven** (preferred): API returns Czech product names/descriptions when `Accept-Language: cs` header is sent. Angular's `HttpClient` can be configured to send this header.
2. **Frontend-driven**: Translation keys for product names in `cs.json`, with a fallback mechanism.

**If backend-driven**, Angular needs:
- An HTTP interceptor that adds `Accept-Language` header to all API requests
- The `ProductService` and related services already use `HttpClient` — the interceptor would apply globally

**If frontend-driven**, you'd need:
- Product name translation keys in `cs.json` (e.g., `"products.hammer-drill": "Vrtací kladivo"`)
- A fallback to English when Czech isn't available (ACC-06)

---

### US3100 - PayU Payment Integration

**Angular mechanism**: Payment method selection in the checkout wizard.

**What exists**:
- `payment.component.html` line 16-23: `<select>` with 5 payment options (bank-transfer, cash-on-delivery, credit-card, buy-now-pay-later, gift-card)
- `payment.component.ts` line 87-107: `updateValidation()` — switches validators based on selected method
- `payment.component.ts` line 142-173: `finishFunction()` — builds payment payload per method
- **PayU is NOT in the list yet**

**Implementation approach**:
1. **Add PayU option** to the `<select>` in `payment.component.html`:
   ```html
   <option value="payu-cz">PayU (Czech Republic)</option>
   ```
2. **Add Czech region detection** — the address component already has `country` field. Use it to conditionally show PayU:
   ```html
   @if (billingCountry === 'CZ') {
     <option value="payu-cz">PayU (Czech Republic)</option>
   }
   ```
3. **Add PayU case** to `updateValidation()` in `payment.component.ts` — no special fields needed for PayU (redirects to external page)
4. **Add PayU case** to `finishFunction()` — build minimal payload
5. **Handle PayU redirect** — after `POST /payment/check` returns success, redirect to PayU payment page (or handle mock response)
6. **Add translations** for "PayU" in `cs.json` and other language files

**Key integration point**: The `checkPayment()` method at line 223 already supports any payment method — it just POSTs to `/payment/check`. The backend must handle `payu-cz`.

---

### US4200 - Delivery Costs

**Angular mechanism**: Delivery cost display in the checkout flow.

**What exists**:
- The checkout wizard has 4 steps: Cart → Login → Address → Payment
- Delivery costs are currently flat (€7.90) — likely calculated server-side
- The address component captures `country` (line 54) which determines the shipping region

**Implementation approach**:
1. **New service**: Create `delivery.service.ts` to fetch delivery options and costs based on address
2. **New component or step**: Either add a delivery selection step to the wizard, or show delivery options within the Address step
3. **Region detection**: Use the `country` field from the address to determine CZ/DACH/EU/US/Others
4. **Weight calculation**: The cart already has quantity and product data — need to add weight data to the Product model
5. **Dynamic pricing**: Display standard + Zásilkovna options with prices based on region + weight tier
6. **Currency handling**: Show CZK when billing country is CZ, USD otherwise

**Files to create/modify**:
- New: `delivery.service.ts`, possibly `delivery/delivery.component.ts`
- Modify: `checkout.component.html` (add step), `address.component.ts` (emit weight data)
- Modify: `payment.component.ts` (include delivery cost in total)

---

### US4350 - Version Number Display

**Angular mechanism**: Footer component.

**What exists**:
- `footer.component.html` — simple copyright line, no version info
- `footer.component.ts` — empty class, no logic

**Implementation approach (Dirt Road — hardcoded)**:
1. Add to `footer.component.html`:
   ```html
   <p>Version 6.0 | Build {{ buildDate }} | Angular {{ angularVersion }}</p>
   ```
2. Add to `footer.component.ts`:
   ```typescript
   buildDate = '2026-07-01';
   angularVersion = '20';
   ```

**Implementation approach (Cobble Stone — git tag)**:
- Use a build-time script to inject the git tag into `environment.ts`
- Footer reads from `environment.version`

**Files to touch**: `footer.component.html`, `footer.component.ts`

---

### US4500 - Register with Google

**Angular mechanism**: New OAuth button on the registration page.

**What exists**:
- `auth/register/register.component.ts` — standard email/password registration form
- `auth/register/register.component.html` — form with fields
- `CustomerAccountService` handles auth calls

**Implementation approach**:
1. **Add Google button** to `register.component.html`:
   ```html
   <button type="button" class="btn btn-outline-danger" (click)="registerWithGoogle()" data-test="google-register">
     <img src="assets/google-icon.svg" /> Continue with Google
   </button>
   ```
2. **Add `registerWithGoogle()` method** to `register.component.ts`:
   - Redirect to Google OAuth consent URL (backend endpoint `/auth/social-login`)
   - Backend handles OAuth flow, returns JWT token
   - Frontend stores token via `CustomerAccountService`
3. **Handle callback**: Add a route like `/auth/callback?token=xxx` that extracts the JWT and logs the user in
4. **GDPR consent**: Add a checkbox before the Google button: "I agree to the privacy policy"

**Files to create/modify**:
- Modify: `register.component.html`, `register.component.ts`
- New route: `auth/callback` in `auth.module.ts`
- New service method: `CustomerAccountService.socialLogin(token)`

---

### US4510 - Automated Regression Tests (Playwright)

**Not Angular code**, but Angular-aware. Tests use `data-test` selectors.

**What exists**:
- `tests/login.spec.ts` — one test: valid login → dashboard
- `playwright.config.ts` — configured for 3 browsers, base URL `localhost:4200`

**Implementation approach**:
1. **Map critical paths** from the feature matrix to test scenarios
2. **Use existing `data-test` selectors** — every component already has them:
   - `data-test="nav-sign-in"`, `data-test="email"`, `data-test="password"`, `data-test="login-submit"`
   - `data-test="payment-method"`, `data-test="finish"`, `data-test="nav-cart"`
   - `data-test="nav-home"`, `data-test="nav-categories"`, `data-test="nav-contact"`
3. **Write tests for each sprint's features**:
   - Sprint 6.0: Logo visible, rentals link removed
   - Sprint 6.1: Czech language switch, PayU in checkout
   - Sprint 6.2: Google register button, delivery cost display

**Pattern to follow** (from existing `login.spec.ts`):
```typescript
test('US1008: Rentals link removed', async ({ page }) => {
  await page.goto('/');
  await expect(page.locator('[data-test="nav-rentals"]')).not.toBeVisible();
});
```

---

### US9100 - Mock PayU Payment Service (Backend, but Angular Touches It)

**Angular's role**: The frontend must read `message` or `error` from the mock response.

**What exists** (already correct for this):
- `payment.component.ts` line 229: `this.paymentMessage = res.message` — reads `message` field
- `payment.component.ts` line 234: `this.paymentError = err.error?.error` — reads `error` field
- `payment.component.html` line 228: `data-test="payment-error-message"` — error alert
- `payment.component.html` line 233: `data-test="payment-success-message"` — success alert

**What needs to change**: Almost nothing in Angular. The existing response handling already supports the mock contract. The backend just needs to implement `POST /mock/payu/orders` and route `payu-cz` through it.

---

### US9200 - PayU TIP Testing UI

**Angular mechanism**: New standalone page with a form.

**Implementation approach**:
1. **New module**: Create `payu-tip/` directory with component
2. **New route**: Add to `app-routing.module.ts`:
   ```typescript
   { path: 'payu-tip', loadChildren: () => import('./payu-tip/payu-tip.module').then(m => m.PayuTipModule) }
   ```
3. **New nav link**: Add to `header.component.html`:
   ```html
   <li class="nav-item" role="menuitem">
     <a class="nav-link" data-test="nav-payu-tip" routerLink="/payu-tip">PayU TIP</a>
   </li>
   ```
4. **Form**: 4 fields (amount, currency, order_id, scenario) + Send button
5. **Modal**: Bootstrap modal showing HTTP status + response body
6. **English-only**: No Transloco on this page

**Files to create**:
- `payu-tip/payu-tip.module.ts`
- `payu-tip/payu-tip.component.ts`
- `payu-tip/payu-tip.component.html`
- `payu-tip/payu-tip.component.css`

---

## Summary: Dependency Map

```
US1003 (Learn Angular) ← prerequisite for everything below
    │
    ├── US1007 (New Logo) ──── header.component.html
    ├── US1008 (Remove Rentals) ── header + products-routing + translations
    ├── US4350 (Version Number) ── footer.component.html
    │
    ├── US2300 (Czech Language) ←── transloco-root + cs.json + header
    │       │
    │       └── US2350 (Czech Products) ← depends on US2300
    │
    ├── US3100 (PayU) ←── payment.component + payment.service
    │       │
    │       └── US9100 (Mock PayU) ←── backend only, Angular already ready
    │               │
    │               └── US9200 (PayU TIP UI) ← new module + header link
    │
    ├── US4200 (Delivery Costs) ← new service + wizard step
    ├── US4500 (Google Register) ← auth/register + new OAuth route
    └── US4510 (Regression Tests) ← Playwright, uses data-test attrs
```