FROM php:7.4.1-fpm-alpine

RUN docker-php-ext-install pdo_mysql

# xdebug
RUN apk add autoconf gcc make g++ zlib-dev && pecl install xdebug-2.8.0 && docker-php-ext-enable xdebug

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer global require hirak/prestissimo

