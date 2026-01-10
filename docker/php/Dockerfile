FROM php:8.4-fpm-alpine

# System + build deps
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    linux-headers \
    $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pdo_mysql \
    intl \
    zip \
    bcmath

# Explicitly enable bundled extensions
# RUN docker-php-ext-enable pdo_sqlite sqlite3

# Composer (optional but useful)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app