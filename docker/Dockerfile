# syntax=docker/dockerfile:1

###############################################################################
# Stage 1 — Composer dependencies
###############################################################################
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock* ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --ignore-platform-reqs

###############################################################################
# Stage 2 — Front-end assets
###############################################################################
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund || npm install --no-audit --no-fund

COPY vite.config.js ./
COPY resources/ resources/
RUN npm run build

###############################################################################
# Stage 3 — Runtime (slim PHP-FPM 8.3 + OPcache)
###############################################################################
FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache \
        icu-libs \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        oniguruma \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        gd \
        zip \
        exif \
        pcntl \
        bcmath \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-ranga.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/10-opcache.ini

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor/ vendor/
COPY --from=assets --chown=www-data:www-data /app/public/build/ public/build/

RUN php artisan storage:link || true

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
