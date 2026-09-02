#!/bin/bash
set -e

# wait mariadb before starting service, for gitlab ci services healtcheck
for i in $(seq 1 60); do
  nc -vz mariadb 3306 && break
  if [ $i -eq 60 ]; then
    echo "stopping"
    exit 1
  fi
  echo "waiting mariadb since $i iterations..."
  sleep 1
done

if [ "$XDEBUG" = "0" ]; then
    rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
fi

exec "$@"
