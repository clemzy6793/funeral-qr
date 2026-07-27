#!/bin/bash
set -e

mkdir -p /var/www/html/storage/uploads /var/www/html/storage/qrcodes /var/www/html/storage/logs
echo "Require all denied" > /var/www/html/storage/.htaccess
# QR code PNGs are public (they encode public event URLs); uploads stay blocked
echo "Require all granted" > /var/www/html/storage/qrcodes/.htaccess
chown -R www-data:www-data /var/www/html/storage

echo "Waiting for PostgreSQL..."
until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -q 2>/dev/null; do
    sleep 2
done
echo "PostgreSQL is ready."

php /var/www/html/migrate.php

php /var/www/html/install.php

php /var/www/html/migrate-legacy.php

exec "$@"
