#!/bin/sh
set -e

export PORT="${PORT:-10000}"

if [ ! -e public/storage ]; then
    php artisan storage:link --relative
fi
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec frankenphp run --config /etc/caddy/Caddyfile
