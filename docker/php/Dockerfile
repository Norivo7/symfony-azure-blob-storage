FROM php:8.3-fpm-bullseye

RUN apt-get update && apt-get install -y \
        git unzip curl \
        libicu-dev libzip-dev; \
    docker-php-ext-install intl opcache zip pdo; \
    pecl install apcu; \
    docker-php-ext-enable apcu; \
    docker-php-ext-install zip

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/app

COPY docker/php/conf.d/*.ini /usr/local/etc/php/conf.d/
