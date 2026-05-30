#syntax=docker/dockerfile:1

FROM dunglas/frankenphp:1-php8.5 AS frankenphp_upstream

FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends file git \
    && install-php-extensions \
        @composer \
        apcu \
        intl \
        opcache \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

COPY frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY frankenphp/Caddyfile /etc/frankenphp/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD php -r 'exit(false === @file_get_contents("http://localhost:2019/metrics", context: stream_context_create(["http" => ["timeout" => 5]])) ? 1 : 0);'

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]

FROM frankenphp_base AS frankenphp_dev

ENV APP_ENV=dev
ENV XDEBUG_MODE=off
ENV FRANKENPHP_WORKER_CONFIG=watch

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        dnsutils \
        iproute2 \
        jq \
        sudo \
    && install-php-extensions xdebug \
    && rm -rf /var/lib/apt/lists/* \
    && git config --system --add safe.directory /app

COPY frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--watch"]

FROM frankenphp_base AS frankenphp_prod_builder

ENV APP_ENV=prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

COPY composer.* symfony.* ./
RUN composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY . ./

RUN mkdir -p var/cache var/log \
    && composer dump-autoload --classmap-authoritative --no-dev \
    && composer dump-env prod \
    && composer run-script --no-dev post-install-cmd \
    && php bin/console asset-map:compile \
    && chmod +x bin/console \
    && chmod -R g=u var

FROM frankenphp_base AS frankenphp_prod

ENV APP_ENV=prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/
COPY --from=frankenphp_prod_builder --chown=www-data:0 /app /app

USER www-data
