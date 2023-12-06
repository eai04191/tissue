FROM php:8.0.30-cli-bullseye as php

RUN apt-get update \
    && apt-get install -y git libpq-dev unzip libicu-dev \
    && docker-php-ext-install pdo_pgsql intl \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . /app

RUN composer install -n --no-dev --prefer-dist --optimize-autoloader

FROM node:16.20.2-bullseye

WORKDIR /app
COPY --from=php /app /app

RUN yarn install \
    && yarn run prod \
    && yarn run doc
