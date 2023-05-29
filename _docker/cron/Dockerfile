FROM php:8.1.1-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

COPY laravel_crontab /etc/crontabs/root

CMD ["crond", "-f"]