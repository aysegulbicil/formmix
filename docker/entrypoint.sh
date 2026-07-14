#!/bin/sh
set -eu

mkdir -p \
    writable/cache \
    writable/logs \
    writable/session \
    writable/uploads \
    writable/debugbar

chown -R www-data:www-data writable

if [ "${1:-}" = "apache2-foreground" ]; then
    echo "FORMMIX veritabanı tabloları kontrol ediliyor..."
    php spark migrate --all
fi

exec "$@"
