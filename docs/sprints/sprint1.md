# Sprint 1 - Product Catalog

The foundation sprint. A basic e-commerce catalog with products, categories, and brands.

## Features

- Product listing with detail pages
- Category tree structure and filtering
- Brand management
- Product images
- Contact form

## API Endpoints

| Method | Endpoint                  | Description              |
|--------|---------------------------|--------------------------|
| GET    | `/brands`                 | List all brands          |
| GET    | `/brands/{id}`            | Get brand by ID          |
| POST   | `/brands`                 | Create brand             |
| PUT    | `/brands/{id}`            | Update brand             |
| DELETE | `/brands/{id}`            | Delete brand             |
| GET    | `/categories`             | List all categories      |
| GET    | `/categories/tree`        | Get category tree        |
| GET    | `/categories/tree/{id}`   | Get subtree by ID        |
| POST   | `/categories`             | Create category          |
| PUT    | `/categories/{id}`        | Update category          |
| DELETE | `/categories/{id}`        | Delete category          |
| GET    | `/products`               | List all products        |
| GET    | `/products/{id}`          | Get product by ID        |
| GET    | `/products/{id}/related`  | Get related products     |
| POST   | `/products`               | Create product           |
| PUT    | `/products/{id}`          | Update product           |
| DELETE | `/products/{id}`          | Delete product           |
| GET    | `/images`                 | List product images      |

## UI Routes

| Path             | Page                 |
|------------------|----------------------|
| `/`              | Product overview     |
| `/product/:id`   | Product detail       |
| `/category/:name`| Products by category |
| `/contact`       | Contact form         |
