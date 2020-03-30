ARG PHP_VERSION=7.2
ARG ALPINE_VERSION=3.11

########################################
#           BASE
########################################
FROM php:${PHP_VERSION}-fpm-alpine${ALPINE_VERSION} as base

# dossier par défaut /app
WORKDIR /app/

# Install extensions
RUN docker-php-ext-install pdo_mysql pcntl opcache
RUN apk add --no-cache git

########################################
#           APP REQUIREMENT
########################################
FROM base AS app-requirement

# Only copy the code we need
COPY web/ /app/web
COPY src/ /app/src
COPY bin/console /app/bin/console
COPY app/ /app/app

COPY composer.json composer.lock /app/

# Needed to install vendors and generate classes autoload
RUN curl --silent --show-error https://getcomposer.org/installer | php \
    && mv /app/composer.phar /bin/composer

########################################
#           DEV
########################################
FROM app-requirement as dev

ENV COMPOSER_ALLOW_SUPERUSER 1
# dossier par défaut /app
WORKDIR /app/

COPY tests /app/tests
COPY phpunit.xml.dist \
    /app/

RUN mkdir /app/var

RUN composer install

RUN APP_ENV=prod bin/console assets:install \
    --env=prod \
    --no-debug

RUN APP_ENV=prod \
    bin/console cache:warmup \
    --env=dev \
    --no-debug \
    && chown -R www-data:www-data /app/var