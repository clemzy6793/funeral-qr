FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libzip-dev unzip sqlite3 \
    && docker-php-ext-install gd zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

RUN echo "upload_max_filesize = 50M\npost_max_size = 55M\nmax_execution_time = 120" > /usr/local/etc/php/conf.d/uploads.ini

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
