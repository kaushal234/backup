FROM php:8.4.22-fpm AS base

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync \
    && install-php-extensions \
       apcu \
       bcmath \
       gd \
       intl \
       opcache \
       mysqli \
       zip

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv ~/.symfony5/bin/symfony /usr/local/bin/symfony

RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/tld.ini

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
        wget \
        unzip \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN echo "date.timezone = America/New_York" >> /usr/local/etc/php/conf.d/symfony.ini

RUN apt-get update \
 && apt-get install -y libnss3 \
      libgbm-dev \
      firefox-esr \
 && wget -q 	https://github.com/mozilla/geckodriver/releases/download/v0.36.0/geckodriver-v0.36.0-linux64.tar.gz \
 && tar -xvzf geckodriver-v0.36.0-linux64.tar.gz -C /usr/local/bin \
 && chown -Rf root:root /usr/local/bin \
 && chmod +x /usr/local/bin/geckodriver \
 && chmod -Rf 777 /usr/local/bin

ENV PANTHER_NO_HEADLESS 0
ENV PANTHER_BROWSER='firefox'
ENV PANTHER_APP_ENV panther

WORKDIR /srv/extranet-new

FROM base AS dev

USER root

FROM base AS prod
