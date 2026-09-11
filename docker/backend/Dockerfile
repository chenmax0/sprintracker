FROM composer:2 AS composer

FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache icu-libs libpq libzip \
    && apk add --no-cache --virtual .build-deps icu-dev libzip-dev postgresql-dev \
    && docker-php-ext-install intl pdo_pgsql opcache zip \
    && apk del .build-deps

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /srv/app

# ---------------------------------------------------------------------------
FROM base AS dev

RUN docker-php-ext-enable opcache
COPY docker/backend/php/dev.ini /usr/local/etc/php/conf.d/zz-app.ini

COPY backend/composer.json backend/composer.lock backend/symfony.lock ./
RUN composer install --prefer-dist --no-scripts --no-autoloader

COPY backend/ .
RUN composer dump-autoload --optimize

CMD ["php-fpm"]

# ---------------------------------------------------------------------------
FROM base AS prod

ENV APP_ENV=prod

COPY docker/backend/php/prod.ini /usr/local/etc/php/conf.d/zz-app.ini

COPY backend/composer.json backend/composer.lock backend/symfony.lock ./
RUN composer install --prefer-dist --no-dev --no-scripts --no-autoloader --no-progress

COPY backend/ .

RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && composer dump-env prod \
    && composer run-script post-install-cmd \
    && chown -R www-data:www-data var

USER www-data

CMD ["php-fpm"]

# ---------------------------------------------------------------------------
FROM nginx:1.27-alpine AS prod-nginx

COPY --from=prod /srv/app/public /srv/app/public
COPY docker/backend/nginx/default.conf /etc/nginx/conf.d/default.conf

CMD ["nginx", "-g", "daemon off;"]
