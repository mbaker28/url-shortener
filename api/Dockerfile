FROM php:8.4-fpm-alpine AS app

WORKDIR /var/www/html

COPY --from=mlocati/php-extension-installer:latest --link /usr/bin/install-php-extensions /usr/local/bin/

RUN apk add --no-cache git zip unzip && \
	install-php-extensions zip gd pdo_mysql @composer-2

EXPOSE 9000
ENV PORT 9000
ENV HOSTNAME "0.0.0.0"

# CMD ["sh", "-c", "composer install; php -S localhost:9000"]