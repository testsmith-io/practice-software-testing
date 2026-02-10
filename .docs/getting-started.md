# Getting Started

## Quick Start

```bash
# 1. Clone the repository
git clone https://github.com/testsmith-io/practice-software-testing.git
cd practice-software-testing

# 2. Set the sprint version (default: sprint5)
echo "SPRINT=sprint5" > .env

# 3. Start all services
docker compose up -d

# 4. Initialize the database (wait ~30s for MariaDB to be ready)
docker compose exec laravel-api php artisan migrate:fresh --seed
```

The first run takes up to 5 minutes depending on your internet connection. Subsequent starts take seconds. All images together are less than 1.5 GB.

## Prerequisites

- [Docker](https://www.docker.com/get-started) and Docker Compose
- Ports `4200`, `8091`, `3306` available on your machine

## Docker Setup

### Local Development (with live-reloading)

```bash
docker compose up -d
```

This mounts source code for live changes and includes optional services from `docker-compose.override.yml` (mailcatcher, cron, phpmyadmin).

### Minimal (without optional services)

```bash
docker compose -f docker-compose.yml up -d
```

Skips mailcatcher, phpmyadmin, and cron.

### Production (prebuilt images)

```bash
docker compose -f docker-compose.prod.yml up --pull missing -d
```

### Services

| Service       | Description                     |
|---------------|---------------------------------|
| `laravel-api` | PHP 8.3-FPM Laravel API         |
| `angular-ui`  | Angular dev server (ng serve)   |
| `web`         | Nginx reverse proxy             |
| `mariadb`     | MariaDB 10.6.11 database        |
| `composer`    | Dependency installation          |

### Local URLs

| URL                                          | Description     |
|----------------------------------------------|-----------------|
| [http://localhost:4200](http://localhost:4200) | Angular App     |
| [http://localhost:8091](http://localhost:8091) | REST API        |
| [http://localhost:8091/api/documentation](http://localhost:8091/api/documentation) | Swagger |
| [http://localhost:1080](http://localhost:1080) | MailCatcher     |
| [http://localhost:8000](http://localhost:8000) | PHPMyAdmin (`root`/`root`) |

## Switching Sprints

Update the `SPRINT` variable in the root `.env` file, then restart:

```bash
# Edit .env to set the desired sprint
echo "SPRINT=sprint4" > .env

# Restart containers and re-seed
docker compose up -d
docker compose exec laravel-api php artisan migrate:fresh --seed
```

Available values: `sprint1`, `sprint2`, `sprint3`, `sprint4`, `sprint5`, `sprint5-with-bugs`, `sprint5-performance`

## Useful Commands

### Database

```bash
# Full reset (drop all tables, re-migrate, re-seed)
docker compose exec laravel-api php artisan migrate:fresh --seed

# Run pending migrations only
docker compose exec laravel-api php artisan migrate

# Seed database (without resetting)
docker compose exec laravel-api php artisan db:seed
```

### API Documentation

```bash
# Generate Swagger docs
docker compose exec laravel-api php artisan l5-swagger:generate
```

### Invoices & Orders

```bash
# Update order statuses
docker compose exec laravel-api php artisan order:update

# Generate PDF invoices
docker compose exec laravel-api php artisan invoice:generate

# Remove PDF invoices
docker compose exec laravel-api php artisan invoice:remove
```

### Logs

```bash
# Tail Laravel logs
docker compose exec laravel-api tail -f storage/logs/laravel.log
```

### Testing

```bash
# Unit tests (Sprint 1-4)
docker compose exec laravel-api ./vendor/bin/phpunit

# Unit tests (Sprint 5)
docker compose exec laravel-api ./vendor/bin/pest

# Unit tests with coverage
docker compose exec laravel-api bash -c "XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html tests/coverage"
```

### Pact Contract Testing

```bash
# Start pact mock service
pact-mock-service start --host localhost --port 7203 \
  --consumer AnyConsumer --provider ProductAPI \
  --pact-dir ./pacts --log ./storage/logs/pact.log

# Stop pact mock service
pact-mock-service stop --port 7203
```
