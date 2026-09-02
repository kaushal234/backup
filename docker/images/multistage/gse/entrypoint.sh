#!/bin/bash
set -e

# --- per-dir ACL with fallback ---
ensure_and_perm() {
  local d="$1"
  install -d -m 0775 "$d"
  local probe="$d/.acl-probe-$$"
  : >"$probe" 2>/dev/null || true
  if command -v setfacl >/dev/null 2>&1 && setfacl -m u:www-data:rw "$probe" >/dev/null 2>&1; then
    setfacl -n  -m u:www-data:rwX -m u:"$(whoami)":rwX -m mask:rwX "$d" 2>/dev/null || true
    setfacl -dn -m u:www-data:rwX -m u:"$(whoami)":rwX -m mask:rwX "$d" 2>/dev/null || true
  else
    chown -R www-data:www-data "$d" 2>/dev/null || true
    chmod -R u+rwX,g+rwX "$d" 2>/dev/null || true
  fi
  rm -f "$probe" 2>/dev/null || true
}

for d in \
  /srv/.composer \
  /srv/alvest-web-portals/intranet/var/cache \
  /srv/alvest-web-portals/intranet/var/log \
  /srv/alvest-web-portals/intranet/legacy/templates_c \
  /srv/alvest-web-portals/shopfloor/templates_c \
  /srv/alvest-web-portals/shopfloor/sessions \
  /srv/alvest-web-portals/shared/inc/templates_c \
  /srv/cache/manuals
do
  ensure_and_perm "$d"
done

chmod 777 /srv/.composer
ls /srv | xargs -I file rm -rf /srv/file/file
ls /srv | xargs -I file mkdir -p /var/www/file
ls /srv | xargs -I file ln -sf /srv/file /var/www/file/current

if [ "$XDEBUG" = "0" ]; then
    rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
fi

FILE=/srv/vendor/autoload.php
if [ -f "$FILE" ]; then
  echo "auto_prepend_file = $FILE" >> /usr/local/etc/php/conf.d/tld.ini
fi;

update-ca-certificates

exec "$@"
