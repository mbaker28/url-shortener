FROM php:8.4.3 AS app

WORKDIR /var/www/html

COPY --from=mlocati/php-extension-installer:latest --link /usr/bin/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install --no-install-recommends -y \
	acl \
	file \
	gettext \
	git \
	&& rm -rf /var/lib/apt/lists/* && \
	install-php-extensions zip gd pdo_mysql @composer

EXPOSE 9000
ENV PORT 9000
ENV HOSTNAME "0.0.0.0"