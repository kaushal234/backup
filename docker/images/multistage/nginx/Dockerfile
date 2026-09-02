FROM nginx:1.24.0 AS base

RUN apt-get update \
 && apt-get install -y netcat-traditional \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# wait php before starting service, for gitlab ci services healtcheck
ADD docker/images/multistage/nginx/wait-php.sh /docker-entrypoint.d/wait-php.sh

FROM base AS dev
# Nothing here, everything is mounted in docker compose

FROM base AS prod
ADD docker/images/multistage/api/default.conf /etc/nginx/conf.d/default.conf
ADD --chown=www-data:www-data . /srv
ADD --chown=www-data:www-dada intranet /intranet
