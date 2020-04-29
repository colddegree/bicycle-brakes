FROM php:7.4.1-fpm-alpine

RUN docker-php-ext-install pdo_mysql

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer global require hirak/prestissimo

