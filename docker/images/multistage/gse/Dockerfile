# Use Buildx to determine the platform dynamically
FROM php:8.3.31-apache AS base

ARG TARGETPLATFORM
ARG TARGETARCH
RUN echo "TARGETPLATFORM=$TARGETPLATFORM TARGETARCH=$TARGETARCH" && uname -m

ENV TZ=America/New_York

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN  sync \
    && install-php-extensions \
        apcu \
        bcmath \
        exif \
        ftp \
        gettext \
        gd \
        intl \
        mysqli \
        opcache \
        pcntl \
        pdo_mysql \
        redis \
        soap \
        zip

RUN apt-get update \
 && export DEBIAN_FRONTEND=noninteractive \
 && apt-get install -y \
        git \
        gnupg \
        openssh-client \
        unzip \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY docker/images/multistage/gse/conf/symfony.ini /usr/local/etc/php/conf.d/symfony.ini
COPY docker/images/multistage/gse/conf/tld.ini /usr/local/etc/php/conf.d/tld.ini

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

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

FROM base AS apache_dev

RUN mkdir -p /usr/share/man/man1 \
       && apt-get update \
       && export DEBIAN_FRONTEND=noninteractive \
       && export ACCEPT_EULA=Y \
       && apt-get install -y --no-install-recommends \
           acl \
           freetds-dev \
           ghostscript \
           fontconfig \
           fonts-wqy-microhei \
           fonts-wqy-zenhei \
           libapache2-mod-xsendfile \
           libaprutil1-dbd-mysql \
           locales \
           pdftk \
           libx11-6 \
           libxext6 \
           libxrender1 \
           libfontconfig1 \
           xfonts-75dpi \
           xfonts-base \
       && locale-gen fr_FR zh_CN \
       && fc-cache -f -v \
       && apt-get clean \
       && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN install-php-extensions \
       pdo_dblib \
       xdebug

#RUN case "${TARGETARCH}" in \
#      "amd64") URL="https://security.debian.org/debian-security/pool/updates/main/o/openssl/libssl1.1_1.1.1w-0+deb11u3_amd64.deb" ;; \
#      "arm64") URL="https://security.debian.org/debian-security/pool/updates/main/o/openssl/libssl1.1_1.1.1w-0+deb11u3_arm64.deb" ;; \
#      *) echo "Unsupported architecture: ${TARGETARCH}"; exit 1 ;; \
#    esac && \
#    echo "Downloading: ${URL}" && \
#    curl -sL "${URL}" -o libssl1.deb && \
#    dpkg -i libssl1.deb && \
#    rm -rf libssl1.deb

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

RUN curl -sL https://salsa.debian.org/fonts-team/fonts-takao/-/raw/debian/00303.01-3/TakaoGothic.ttf -o TakaoGothic.ttf \
 && mv TakaoGothic.ttf /usr/share/fonts/

ADD docker/images/multistage/gse/vhost.conf /etc/apache2/sites-available/app.conf
ADD docker/images/multistage/gse/entrypoint.sh /usr/bin/entrypoint.sh

RUN a2enmod rewrite \
       && a2enmod xsendfile \
       && a2enmod dbd \
       && a2enmod authn_dbd \
       && a2enmod authz_dbd \
       && a2ensite app.conf \
       && a2dissite 000-default

RUN set -eux; \
  echo "date.timezone = America/New_York" >> /usr/local/etc/php/conf.d/symfony.ini && \
  echo "include_path = /var/www/alvest-web-portals/current/shared/lib/src:/var/www/alvest-web-portals/current/shared/inc:/var/www/alvest-web-portals/current/shared/inc/smarty:." >> /usr/local/etc/php/conf.d/tld.ini

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv ~/.symfony5/bin/symfony /usr/local/bin/symfony

# dev
FROM apache_dev AS dev

ADD docker/config/gse-php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]

# web_root
FROM base AS web_root
WORKDIR /srv/alvest-web-portals/
ADD composer.json composer.lock .
RUN --mount=type=secret,id=composer_auth \
    COMPOSER_AUTH="$(cat /run/secrets/composer_auth)" \
    composer install \
      --no-interaction \
      -n \
      --no-progress \
      --prefer-dist

# intranet
FROM web_root AS intranet
WORKDIR /srv/alvest-web-portals/intranet
ADD intranet/composer.json intranet/composer.lock .
ADD intranet .
ADD packages ../packages
RUN bin/install-keys.sh
RUN --mount=type=secret,id=composer_auth \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_AUTH="$(cat /run/secrets/composer_auth)" \
    composer install \
      -n \
      --no-interaction \
      --no-progress \
      --prefer-dist
ENV WEBPACK_API_URI=http://api
RUN yarn install --frozen-lockfile --network-timeout 600000
RUN yarn encore production
ADD intranet/legacy/templates_c legacy/templates_c


# shared/inc
FROM web_root AS shared_inc
WORKDIR /srv/alvest-web-portals/shared/inc
ADD shared/inc/composer.json shared/inc/composer.lock ./
RUN --mount=type=secret,id=composer_auth \
    COMPOSER_AUTH="$(cat /run/secrets/composer_auth)" \
    composer install \
      -n \
      --no-interaction \
      --no-progress \
      --prefer-dist
ADD shared/inc .

# admin
FROM web_root AS admin
WORKDIR /srv/alvest-web-portals/admin
ADD admin/composer.json admin/composer.lock ./
RUN --mount=type=secret,id=composer_auth \
    COMPOSER_AUTH="$(cat /run/secrets/composer_auth)" \
    composer install \
      -n \
      --no-interaction \
      --no-progress \
      --prefer-dist
ADD admin .

# shopfloor
FROM web_root AS shopfloor
ADD packages ./packages
WORKDIR /srv/alvest-web-portals/shopfloor
ADD shopfloor/composer.json shopfloor/composer.lock ./
RUN --mount=type=secret,id=composer_auth \
    COMPOSER_AUTH="$(cat /run/secrets/composer_auth)" \
    composer install \
      -n \
      --no-interaction \
      --no-progress \
      --prefer-dist
ADD shopfloor .

# packages
FROM web_root AS packages
WORKDIR /srv/alvest-web-portals/packages
ADD packages .

# prod image
FROM apache_dev AS prod
WORKDIR /srv/alvest-web-portals

COPY --from=intranet /srv/alvest-web-portals/intranet /srv/alvest-web-portals/intranet
COPY --from=shared_inc /srv/alvest-web-portals/shared/inc /srv/alvest-web-portals/shared/inc
COPY --from=admin /srv/alvest-web-portals/admin /srv/alvest-web-portals/admin
COPY --from=shopfloor /srv/alvest-web-portals/shopfloor /srv/alvest-web-portals/shopfloor
COPY --from=packages /srv/alvest-web-portals/packages /srv/alvest-web-portals/packages

# This parameter allow application to work in the CI with Selenium
ENV XDEBUG=0

WORKDIR /var/www

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
