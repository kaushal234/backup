FROM mariadb:10.11.14 AS base

ENV TZ=America/New_York
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

FROM base AS dev


FROM base AS prod
ADD var-lib-mysql.tar /var/lib/mysql
