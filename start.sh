#!/bin/sh
set -e

echo "Starting AlBaik Store..."

php artisan optimize:clear

php artisan migrate --force

php artisan storage:link || true

php artisan config:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
