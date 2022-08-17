FROM php:8.1-cli-alpine

MAINTAINER Thiago Rodrigues (xthiago) <me@xthiago.com>

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER="1"
ENV PATH="/app/bin:/app/vendor/bin:${PATH}"

RUN apk add --no-cache --update \
    git \
    curl \
    $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY docker/php/conf.d/ $PHP_INI_DIR/conf.d/

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY ./composer.* /app

RUN composer install \
    --optimize-autoloader \
    --no-ansi \
    --no-interaction \
    --no-progress \
    --no-suggest

EXPOSE 8000
