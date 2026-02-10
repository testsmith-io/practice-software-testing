# Sprint 4 - Auth & Accounts

Adds authenticated user areas with route protection, account management, and contract testing.

## New Features

- Login and registration pages
- Protected account area (profile, invoices, favorites, messages)
- Route guards (`UserAuthGuard`) for authenticated-only pages
- Lazy-loaded modules for auth and account sections
- PATCH support for partial updates on resources
- Database refresh endpoint for testing
- Pact contract testing integration

## New API Endpoints

| Method | Endpoint            | Description                  |
|--------|---------------------|------------------------------|
| POST   | `/refresh`          | Reset database (migrate + seed) |
| PATCH  | `/brands/{id}`      | Partial update brand         |
| PATCH  | `/categories/{id}`  | Partial update category      |
| PATCH  | `/invoices/{id}`    | Partial update invoice       |
| PATCH  | `/products/{id}`    | Partial update product       |
| PATCH  | `/users/{id}`       | Partial update user          |

## UI Routes

| Path             | Page                     | New? |
|------------------|--------------------------|------|
| `/`              | Product overview         |      |
| `/product/:id`   | Product detail           |      |
| `/category/:name`| Products by category     |      |
| `/rentals`       | Rental products overview |      |
| `/checkout`      | Checkout                 |      |
| `/contact`       | Contact form             |      |
| `/auth`          | Login / Register (lazy)  | Yes  |
| `/account`       | Account panel (lazy)     | Yes  |

### Account Panel Pages

- Profile management
- Invoice history and details
- Favorites list
- Messages / contact requests
