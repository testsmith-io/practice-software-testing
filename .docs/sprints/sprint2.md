# Sprint 2 - Users & Search

Adds user authentication, invoicing, favorites, contact messaging, reports, and search capabilities.

## New Features

- User registration and JWT-based login
- Password management (change, forgot)
- Search endpoints for products, brands, and categories
- Invoice creation and management
- Favorites (save products)
- Contact messaging system with replies and file attachments
- Payment validation
- Reporting (sales, customers, top products)

## New API Endpoints

### Users

| Method | Endpoint                  | Description              |
|--------|---------------------------|--------------------------|
| POST   | `/users/login`            | Authenticate user        |
| POST   | `/users/register`         | Register new user        |
| POST   | `/users/change-password`  | Change password          |
| POST   | `/users/forgot-password`  | Request password reset   |
| GET    | `/users/logout`           | Logout                   |
| GET    | `/users/me`               | Get current user profile |
| GET    | `/users/refresh`          | Refresh JWT token        |
| GET    | `/users`                  | List all users           |
| GET    | `/users/{id}`             | Get user by ID           |
| GET    | `/users/search`           | Search users             |
| PUT    | `/users/{id}`             | Update user              |
| DELETE | `/users/{id}`             | Delete user              |

### Invoices

| Method | Endpoint                   | Description              |
|--------|----------------------------|--------------------------|
| GET    | `/invoices`                | List invoices            |
| GET    | `/invoices/{id}`           | Get invoice by ID        |
| GET    | `/invoices/search`         | Search invoices          |
| POST   | `/invoices`                | Create invoice           |
| PUT    | `/invoices/{id}`           | Update invoice           |
| PUT    | `/invoices/{id}/status`    | Update invoice status    |
| DELETE | `/invoices/{id}`           | Delete invoice           |

### Favorites

| Method | Endpoint            | Description              |
|--------|---------------------|--------------------------|
| GET    | `/favorites`        | List user favorites      |
| GET    | `/favorites/{id}`   | Get favorite by ID       |
| POST   | `/favorites`        | Add favorite             |
| PUT    | `/favorites/{id}`   | Update favorite          |
| DELETE | `/favorites/{id}`   | Remove favorite          |

### Messages

| Method | Endpoint                       | Description              |
|--------|--------------------------------|--------------------------|
| GET    | `/messages`                    | List messages            |
| GET    | `/messages/{id}`               | Get message by ID        |
| POST   | `/messages`                    | Send message             |
| POST   | `/messages/{id}/attach-file`   | Attach file to message   |
| POST   | `/messages/{id}/reply`         | Reply to message         |
| PUT    | `/messages/{id}/status`        | Update message status    |

### Reports

| Method | Endpoint                                  | Description                    |
|--------|-------------------------------------------|--------------------------------|
| GET    | `/reports/total-sales-of-years`           | Total sales by year            |
| GET    | `/reports/total-sales-per-country`        | Sales by country               |
| GET    | `/reports/top10-purchased-products`       | Top 10 purchased products      |
| GET    | `/reports/top10-best-selling-categories`  | Top 10 selling categories      |
| GET    | `/reports/customers-by-country`           | Customer distribution          |
| GET    | `/reports/average-sales-per-month`        | Monthly average sales          |
| GET    | `/reports/average-sales-per-week`         | Weekly average sales           |

### Search & Payment

| Method | Endpoint              | Description              |
|--------|-----------------------|--------------------------|
| GET    | `/brands/search`      | Search brands            |
| GET    | `/categories/search`  | Search categories        |
| GET    | `/products/search`    | Search products          |
| POST   | `/payment/check`      | Validate payment         |

## UI Routes

Same as Sprint 1 (no new UI pages).
