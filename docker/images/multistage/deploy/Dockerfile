FROM php:8.3-cli-bookworm AS base

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      openssh-client git bash rsync ca-certificates zip unzip \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /srv
CMD ["tail", "-f", "/dev/null"]

FROM base AS dev
FROM base AS prod