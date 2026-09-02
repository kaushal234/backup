#!/bin/bash
for i in $(seq 1 60); do
  nc -vz php-api 9000 && break
  if [ $i -eq 60 ]; then
    echo "stopping"
    exit 1
  fi
  echo "waiting php-api since $i iterations..."
  sleep 1
done

