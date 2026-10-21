#!/bin/sh

set -eu

: "${PORT:=10000}"
: "${RESET_DATABASE:=false}"

if [ "$RESET_DATABASE" = "true" ]; then
    echo "WARNING: RESET_DATABASE=true. Rebuilding database from scratch."

    php artisan migrate:fresh --force
    php artisan db:seed --force

    echo "Database initialization completed."
fi

envsubst '${PORT}' \
    < /etc/nginx/templates/default.conf.template \
    > /etc/nginx/conf.d/default.conf

exec /usr/bin/supervisord \
    -c /etc/supervisor/conf.d/supervisord.conf
