FROM php:8.4-fpm-alpine

# System deps
RUN apk add --no-cache \
    bash git unzip icu-dev libzip-dev

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql intl zip

# Composer (optional but useful)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app