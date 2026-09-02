# Use Buildx to determine the platform dynamically
FROM php:8.4.24-fpm AS base

ARG TARGETPLATFORM
ARG TARGETARCH
RUN echo "TARGETPLATFORM=$TARGETPLATFORM TARGETARCH=$TARGETARCH" && uname -m

ARG UID=1000
ARG GID=1000

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN  sync \
    && install-php-extensions \
       apcu \
       gd \
       intl \
       opcache \
       pdo_dblib\
       pdo_mysql \
       soap \
       xsl \
       zip \
       sysvsem \
       calendar

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv ~/.symfony5/bin/symfony /usr/local/bin/symfony

RUN apt-get update \
 && export DEBIAN_FRONTEND=noninteractive \
 && apt-get install -y --no-install-recommends \
        ghostscript \
        gnupg \
        xorg  \
        fontconfig \
        fonts-wqy-microhei \
        fonts-wqy-zenhei \
        unzip \
        git \
        netcat-traditional \
        mariadb-client \
 && fc-cache -f -v \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN ARCH=${TARGETARCH} && \
    case "$ARCH" in \
      "amd64") URL="https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox_0.12.6.1-3.bookworm_amd64.deb" ;; \
      "arm64") URL="https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox_0.12.6.1-3.bookworm_arm64.deb" ;; \
      *) echo "Unsupported architecture: $ARCH" && exit 1 ;; \
    esac && \
    echo "Downloading: $URL" && \
    curl -sL "$URL" -o wkhtmltox.deb && \
    dpkg -i wkhtmltox.deb && \
    rm -rf wkhtmltox.deb

# Node.js 22 from NodeSource (modern keyring + deb line)
RUN set -eux; \
    mkdir -p /etc/apt/keyrings; \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
      | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg; \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_22.x nodistro main" \
      > /etc/apt/sources.list.d/nodesource.list; \
    apt-get update; \
    apt-get install -y --no-install-recommends nodejs; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Yarn 1.x pinned
RUN npm i -g yarn@1.22.22 \
 && ln -sf /usr/local/bin/yarn /usr/local/bin/yarnpkg \
 && yarn -v

ADD docker/images/multistage/api/freetds.conf /etc/freetds/freetds.conf

RUN echo "memory_limit = -1"  >> /usr/local/etc/php/conf.d/tld.ini
RUN echo "date.timezone = America/New_York" >> /usr/local/etc/php/conf.d/tld.ini
RUN echo "post_max_size = 32M" >> /usr/local/etc/php/conf.d/tld.ini
RUN echo "upload_max_filesize = 32M" >> /usr/local/etc/php/conf.d/tld.ini
RUN echo "apc.shm_size = 256M" >> /usr/local/etc/php/conf.d/tld.ini

RUN chown www-data: /srv /var/www
WORKDIR /srv/api

ADD docker/images/multistage/api/entrypoint.sh /usr/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]
USER www-data

FROM base AS dev

USER root

RUN install-php-extensions \
    xdebug

RUN echo "memory_limit = 2G" >> /usr/local/etc/php/conf.d/symfony.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/symfony.ini

RUN apt-get update \
 && export DEBIAN_FRONTEND=noninteractive \
 && apt-get install -y --no-install-recommends \
        acl \
        cups-bsd \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN chmod a+w -R /usr/local/etc/php/conf.d/

RUN groupmod -g $GID www-data \
    && usermod -u $UID www-data
USER www-data

FROM base AS prod
ADD --chown=www-data:www-data . /srv
RUN yarn install --frozen-lockfile --network-timeout 600000 --cache-folder=.cache && rm -rf .cache
# TODO: find missing file to compose install before adding all sources
RUN bin/install-keys.sh
RUN yarn encore production
RUN --mount=type=secret,id=composer_auth \
    COMPOSER_AUTH="$(cat /run/secrets/composer_auth)" \
    composer install \
        --no-interaction \
        --prefer-dist \
        --no-progress \
        --optimize-autoloader \
        --classmap-authoritative
