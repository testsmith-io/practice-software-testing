# Sprint 5 - Full Platform

The complete production version with shopping cart, admin dashboard, social auth, 2FA, PDF invoices, multiple payment methods, and multi-language support.

## New Features

- **Shopping Cart** - Add/remove items, update quantities
- **Social Login** - Google and GitHub OAuth
- **Two-Factor Authentication** - TOTP setup and verification
- **PDF Invoices** - Generate and download invoice PDFs
- **Multiple Payment Methods** - Credit card, bank transfer, buy-now-pay-later, gift card, cash on delivery
- **Admin Dashboard** - Full management of products, invoices, users, categories, brands, and reports
- **Chat Widget** - In-app support chat
- **Multi-language** - Transloco i18n support
- **Privacy Policy** page

### Platform Upgrades

- Laravel 11 &rarr; Laravel 12
- PHP 8.1 &rarr; PHP 8.3
- PHPUnit &rarr; Pest testing framework
- Hash routing &rarr; clean URLs with scroll restoration
- Full lazy-loading for all route modules

## New API Endpoints

### Cart

| Method | Endpoint                              | Description              |
|--------|---------------------------------------|--------------------------|
| POST   | `/carts`                              | Create cart              |
| POST   | `/carts/{id}`                         | Add item to cart         |
| GET    | `/carts/{id}`                         | Get cart contents        |
| PUT    | `/carts/{id}/product/quantity`        | Update item quantity     |
| DELETE | `/carts/{cartId}/product/{productId}` | Remove item from cart    |
| DELETE | `/carts/{cartId}`                     | Delete cart              |

### Invoice Downloads

| Method | Endpoint                              | Description              |
|--------|---------------------------------------|--------------------------|
| GET    | `/invoices/{id}/download-pdf`         | Download invoice PDF     |
| GET    | `/invoices/{id}/download-pdf-status`  | Check PDF generation status |

### Social Authentication

| Method | Endpoint                  | Description              |
|--------|---------------------------|--------------------------|
| GET    | `/auth/social-login`      | Initiate social login    |
| GET    | `/auth/cb/google`         | Google OAuth callback    |
| GET    | `/auth/cb/github`         | GitHub OAuth callback    |

### TOTP (Two-Factor Auth)

| Method | Endpoint            | Description              |
|--------|---------------------|--------------------------|
| POST   | `/totp/setup`       | Set up 2FA               |
| POST   | `/totp/verify`      | Verify TOTP code         |
| POST   | `/totp/login/totp`  | Login with TOTP          |

## UI Routes

| Path        | Module          | Description                |
|-------------|-----------------|----------------------------|
| `/`         | ProductsModule  | Product browsing (lazy)    |
| `/privacy`  | PrivacyModule   | Privacy policy (lazy)      |
| `/checkout` | CheckoutModule  | Checkout flow (lazy)       |
| `/contact`  | ContactModule   | Contact form (lazy)        |
| `/auth`     | AuthModule      | Login / Register (lazy)    |
| `/account`  | AccountModule   | User account panel (lazy)  |
| `/admin`    | AdminModule     | Admin dashboard (lazy)     |

## Feature Comparison Across Sprints

| Feature                | Sprint 1 | Sprint 2 | Sprint 3 | Sprint 4 | Sprint 5 |
|------------------------|:--------:|:--------:|:--------:|:--------:|:--------:|
| Products / Categories  | x        | x        | x        | x        | x        |
| Brands                 | x        | x        | x        | x        | x        |
| Contact Form           | x        | x        | x        | x        | x        |
| User Auth (JWT)        |          | x        | x        | x        | x        |
| Search                 |          | x        | x        | x        | x        |
| Invoices               |          | x        | x        | x        | x        |
| Favorites              |          | x        | x        | x        | x        |
| Reports                |          | x        | x        | x        | x        |
| Checkout               |          |          | x        | x        | x        |
| Rentals                |          |          | x        | x        | x        |
| Login / Register Pages |          |          |          | x        | x        |
| Account Panel          |          |          |          | x        | x        |
| Route Guards           |          |          |          | x        | x        |
| PATCH Endpoints        |          |          |          | x        | x        |
| Shopping Cart          |          |          |          |          | x        |
| Social Login           |          |          |          |          | x        |
| 2FA / TOTP             |          |          |          |          | x        |
| PDF Invoices           |          |          |          |          | x        |
| Multiple Payments      |          |          |          |          | x        |
| Admin Dashboard        |          |          |          |          | x        |
| Chat Widget            |          |          |          |          | x        |
| Multi-language         |          |          |          |          | x        |
