#!/bin/bash
set -e

mkdir -p /var/www/html/storage/uploads /var/www/html/storage/qrcodes
echo "Deny from all" > /var/www/html/storage/.htaccess
chown -R www-data:www-data /var/www/html/storage

php /var/www/html/install.php

exec "$@"
