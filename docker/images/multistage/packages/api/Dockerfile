FROM php:8.3.31-fpm AS base

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync \
    && install-php-extensions \
       apcu \
       gd \
       intl \
       mbstring \
       opcache \
       pdo_dblib\
       pdo_mysql \
       soap \
       xsl \
       zip

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /srv

FROM base AS dev

RUN install-php-extensions xdebug

RUN echo "memory_limit = 2G" >> /usr/local/etc/php/conf.d/symfony.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/symfony.ini

ADD packages/twig_helper/composer-api.json /srv/composer.json
RUN --mount=type=secret,id=composer_auth \
    COMPOSER_AUTH="$(cat /run/secrets/composer_auth)" \
    composer install \
      --no-cache \
      --no-interaction \
      --optimize-autoloader \
      --no-progress \
      --classmap-authoritative \
      --prefer-dist

FROM base AS prod

ADD --chown=www-data:www-data packages/twig_helper /srv
