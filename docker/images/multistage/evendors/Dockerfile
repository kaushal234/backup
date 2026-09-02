# Use Buildx to determine the platform dynamically
FROM php:8.4.22-fpm AS base

ARG TARGETPLATFORM
ARG TARGETARCH
RUN echo "Detected TARGETPLATFORM=${TARGETPLATFORM} TARGETARCH=${TARGETARCH}" && uname -m

ARG UID=1000
ARG GID=1000

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN  sync \
    && install-php-extensions \
       apcu \
       bcmath \
       gd \
       intl \
       opcache \
       zip

RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/tld.ini
RUN echo "date.timezone = America/New_York" >> /usr/local/etc/php/conf.d/tld.ini

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv ~/.symfony5/bin/symfony /usr/local/bin/symfony

RUN apt-get update \
 && export DEBIAN_FRONTEND=noninteractive \
 && apt-get install -y --no-install-recommends \
        wget \
        git \
        gnupg \
        unzip \
        libnss3 \
        libgbm-dev \
        firefox-esr \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


# Firefox and GeckoDriver for Panther E2E tests
ARG GECKODRIVER_VERSION=0.36.0
RUN set -eux; \
    case "${TARGETARCH}" in \
      amd64)  SUFFIX="linux64" ;; \
      arm64)  SUFFIX="linux-aarch64" ;; \
      *) echo "Unsupported TARGETARCH: ${TARGETARCH}"; exit 1 ;; \
    esac; \
    URL="https://github.com/mozilla/geckodriver/releases/download/v${GECKODRIVER_VERSION}/geckodriver-v${GECKODRIVER_VERSION}-${SUFFIX}.tar.gz"; \
    echo "Downloading: ${URL}"; \
    curl -fsSL "${URL}" -o /tmp/geckodriver.tgz; \
    tar -xzf /tmp/geckodriver.tgz -C /usr/local/bin geckodriver; \
    rm -f /tmp/geckodriver.tgz; \
    chmod 0755 /usr/local/bin/geckodriver; \
    geckodriver --version

ENV PANTHER_NO_HEADLESS 0
ENV PANTHER_BROWSER='firefox'
ENV PANTHER_APP_ENV panther

WORKDIR /srv/evendors

FROM base AS dev

FROM base AS prod

ADD --chown=www-data:www-data . /srv

RUN --mount=type=secret,id=composer_auth \
    COMPOSER_AUTH="$(cat /run/secrets/composer_auth)" \
    composer install \
      --no-interaction \
      --optimize-autoloader \
      --no-progress \
      --classmap-authoritative \
      --prefer-dist
