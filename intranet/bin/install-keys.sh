#!/usr/bin/env bash
set -e

if [ ! -f config/jwt/private.pem ]; then
    cp config/jwt/public.pem.dist config/jwt/public.pem
fi
