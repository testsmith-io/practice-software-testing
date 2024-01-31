# Default accounts

| First name | Last name | Role   | E-mail                                | Password   |
|------------|-----------|--------|---------------------------------------|------------|
| John       | Doe       | admin  | admin@practicesoftwaretesting.com     | welcome01  |
| Jane       | Doe       | user   | customer@practicesoftwaretesting.com  | welcome01  |
| Jack       | Howe      | user   | customer2@practicesoftwaretesting.com | welcome01  |

# URLs (hosted versions)

| Description          | Application                                                                                    | API                                                                                                           | Swagger                                                                                                                  |
|----------------------|------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------|
| Sprint 1             | [https://v1.practicesoftwaretesting.com](https://v1.practicesoftwaretesting.com)               | [https://api-v1.practicesoftwaretesting.com](https://api-v1.practicesoftwaretesting.com/status)               | [https://api-v1.practicesoftwaretesting.com](https://api-v1.practicesoftwaretesting.com/api/documentation)               |
| Sprint 2             | [https://v2.practicesoftwaretesting.com](https://v2.practicesoftwaretesting.com)               | [https://api-v2.practicesoftwaretesting.com](https://api-v2.practicesoftwaretesting.com/status)               | [https://api-v2.practicesoftwaretesting.com](https://api-v2.practicesoftwaretesting.com/api/documentation)               |
| Sprint 3             | [https://v3.practicesoftwaretesting.com](https://v3.practicesoftwaretesting.com)               | [https://api-v3.practicesoftwaretesting.com](https://api-v3.practicesoftwaretesting.com/status)               | [https://api-v3.practicesoftwaretesting.com](https://api-v3.practicesoftwaretesting.com/api/documentation)               |
| Sprint 4             | [https://v4.practicesoftwaretesting.com](https://v4.practicesoftwaretesting.com)               | [https://api-v4.practicesoftwaretesting.com](https://api-v4.practicesoftwaretesting.com/status)               | [https://api-v4.practicesoftwaretesting.com](https://api-v4.practicesoftwaretesting.com/api/documentation)               |
| Sprint 5             | [https://practicesoftwaretesting.com](https://practicesoftwaretesting.com)                     | [https://api.practicesoftwaretesting.com](https://api.practicesoftwaretesting.com/status)                     | [https://api.practicesoftwaretesting.com](https://api.practicesoftwaretesting.com/api/documentation)                     |
| Sprint 5 (with bugs) | [https://with-bugs.practicesoftwaretesting.com](https://with-bugs.practicesoftwaretesting.com) | [https://api-with-bugs.practicesoftwaretesting.com](https://api-with-bugs.practicesoftwaretesting.com/status) | [https://api-with-bugs.practicesoftwaretesting.com](https://api-with-bugs.practicesoftwaretesting.com/api/documentation) |


# Using the docker containers

I will take up to 5 minutes (depending on the speed of your internet connection), if you run `docker-compose up -d` for the first
time. Any subsequent `docker-compose up -d` will take seconds.

All images together are less than 1,5 GB.

## URL's (local version)

| URL                                                                                | Description           |
|------------------------------------------------------------------------------------|-----------------------|
| [http://localhost:8091](http://localhost:8091)                                     | (REST) API            |
| [http://localhost:8091/api/documentation](http://localhost:8091/api/documentation) | Swagger               |
| [http://localhost:1080](http://localhost:1080)                                     | MailCatcher           |
| [http://localhost:4200](http://localhost:4200)                                     | (Angular) Application |
| [http://localhost:8000](http://localhost:8000) (`root`/`root`)                     | PHPMyAdmin            |

## Switch sprint

Update the `SPRINT_FOLDER` in [.env](.env) to use the proper version that belongs to the sprint.

## Roll Back - Run Migrations - Seed Database

`docker-compose exec laravel-api php artisan migrate:refresh --seed`

## Migrate database schema

`docker-compose exec laravel-api php artisan migrate`

## Seed database

`docker-compose exec laravel-api php artisan db:seed`

## Generate Swagger documentation

`docker-compose exec laravel-api php artisan l5-swagger:generate`

## Update order status

`docker-compose exec laravel-api php artisan order:update`

## Remove PDF documents

`docker-compose exec laravel-api php artisan invoice:remove`

## Generate PDF documents

`docker-compose exec laravel-api php artisan invoice:generate`

## Execute unit tests

`./vendor/bin/phpunit`

## Execute unit tests with coverage

` XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html tests/coverage`


# Sprints

## Sprint 0

During this initial sprint we took some architectural decisions. We decided to implement a
super-fast [Laravel](https://laravel.com/) API, as wel as an [Angular](https://angular.io/) frontend.

Every developer or tester is able to spin up the environment on its own machine. This makes testing easier, and it
allows you to manipulate data.

The deliverable of Sprint0 is a Dockerized environment as wel as database seeding scripts. Basically, the end-result is
an empty environment.
