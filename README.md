# Using the docker image

## Switch sprint

Update the `SPRINT_FOLDER` in [.env](.env) to use the proper version that belongs to the sprint.

I will take up to 5 minutes (depending on your internet connection), if you run `docker-compose up -d` for the first
time. Any subsequent `docker-compose up -d` will take seconds.

All images are less than 1,5 GB.

# URL's (hosted)

# URL's (local)

| URL                                                                                | Description           |
|------------------------------------------------------------------------------------|-----------------------|
| [http://localhost:8091](http://localhost:8091)                                     | (REST) API            |
| [http://localhost:8091/api/documentation](http://localhost:8091/api/documentation) | Swagger               |
| [http://localhost:8025](http://localhost:8025)                                     | MailHog               |
| [http://localhost:4200](http://localhost:4200)                                     | (Angular) Application |
| [http://localhost:8000](http://localhost:8000) (`root`/`root`)                     | PHPMyAdmin            |

# Migrate database schema

`docker-compose exec lumen-api php artisan migrate`

# Seed database

`docker-compose exec lumen-api php artisan db:seed`

# Generate Swagger documentation

`docker-compose exec lumen-api php artisan swagger-lume:generate`

# Execute unittests

`./vendor/bin/phpunit`

# Execute unittests with coverage

`./vendor/bin/phpunit --coverage-html report-coverage`

# Default account

| First name | Last name   | Role   | E-mail                               | Password   |
|------------|-------------|--------|--------------------------------------|------------|
| John       | Doe         | admin  | admin@practicesoftwaretesting.com    | welcome01  |
| Jane       | Doe         | user   | customer@practicesoftwaretesting.com | welcome01  |

# Sprints

## Sprint 0

During this initial sprint we took some architectural decisions. We decided to implement a
super-fast [Lumen](https://lumen.laravel.com) API, as wel as an [Angular](https://angular.io/) frontend.

Every developer or tester is able to spin up the environment on its own machine. This makes testing easier, and it
allows you to manipulate data.

The deliverable of Sprint0 is a Dockerized environment as wel as database seeding scripts. Basically, the end-result is
an empty environment.

# Naming convention

