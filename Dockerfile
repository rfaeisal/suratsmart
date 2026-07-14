FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libzip-dev libgmp-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip gmp \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

RUN mkdir -p uploads/pdf/surat uploads/pdf/resep uploads/pdf/tmp \
    application/sessions application/logs application/cache \
    && chmod -R 777 uploads application/sessions application/logs application/cache

COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
CMD ["/usr/local/bin/docker-entrypoint.sh"]
