#!/bin/sh

set -eu

: "${PORT:=10000}"

envsubst '${PORT}' \
    < /etc/nginx/templates/default.conf.template \
    > /etc/nginx/conf.d/default.conf

exec /usr/bin/supervisord \
    -c /etc/supervisor/conf.d/supervisord.conf
