#!/bin/sh

# Laravel-specific directories, check before acting
[ -d /var/www/html/storage ] && chown -R www-data:www-data /var/www/html/storage && chmod -R 775 /var/www/html/storage
[ -d /var/www/html/bootstrap/cache ] && chown -R www-data:www-data /var/www/html/bootstrap/cache && chmod -R 775 /var/www/html/bootstrap/cache

exec "$@"