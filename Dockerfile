FROM php:8.1-fpm-alpine AS app_php

WORKDIR /var/www/html

COPY --from=mlocati/php-extension-installer:latest --link /usr/bin/install-php-extensions /usr/local/bin/

RUN apk add --no-cache git zip unzip && \
	install-php-extensions zip gd pdo_mysql @composer-2