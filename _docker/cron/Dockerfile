FROM php:8.1.1-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

COPY lumen_crontab /etc/crontabs/root

CMD ["crond", "-f"]