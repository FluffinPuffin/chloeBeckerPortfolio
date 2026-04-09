#!/bin/bash
set -e

NGINX_CONF="/etc/nginx/conf.d/default.conf"

if [ -f "$NGINX_CONF" ]; then
    sed -i '/location \/ {/i\    location ~* \.(db|sh|toml|env|json|sql|log|gitignore)$ { deny all; return 404; }' "$NGINX_CONF"
fi

php-fpm -D && nginx -g 'daemon off;'
