# Stage 1: Build assets (Vite/NPM)
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Install PHP dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-req=php

# Stage 3: Final runtime image
# Dikunci ke 8.2.35 (patch PHP2Shell, CVE-2026-109618 & CVE-2026-109619).
# Mengganti tag ini juga memaksa build menarik image dasar baru, tidak memakai
# lapisan cache lama. Naikkan secara berkala (cek php.net / hub.docker.com).
FROM php:8.2.35-apache

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql bcmath gd zip opcache

# Install ekstensi Redis (phpredis) via PECL. Diperlukan HANYA jika driver
# cache/session/queue diarahkan ke redis (mis. REDIS eksternal). $PHPIZE_DEPS
# adalah build-tools sementara yang langsung di-purge agar image tetap ramping.
RUN apt-get update && apt-get install -y --no-install-recommends $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# Konfigurasi OPcache (ekstensinya sudah di-install di RUN pertama, saat build-tools
# bawaan image masih tersedia). Image php:*-apache TIDAK mengaktifkan OPcache secara
# default, sehingga PHP mengompilasi ulang seluruh file Laravel pada SETIAP request.
# validate_timestamps=0 aman karena kode di dalam image bersifat immutable
# (tidak pernah berubah saat runtime); cache di-reset saat container di-deploy ulang.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.revalidate_freq=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Enable Apache mod_rewrite for Laravel
RUN a2enmod rewrite

# Set ServerName global untuk menghilangkan warning AH00558
# ("Could not reliably determine the server's fully qualified domain name").
# Murni kosmetik agar log bersih; tidak mempengaruhi fungsi.
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .
# Copy built assets from Stage 1
COPY --from=assets /app/public/build ./public/build
# Copy vendors from Stage 2
COPY --from=vendor /app/vendor ./vendor

# Install composer in final stage to run dump-autoload
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Set Apache Document Root to /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's#\/var\/www\/html#${APACHE_DOCUMENT_ROOT}#g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's#\/var\/www\/html#${APACHE_DOCUMENT_ROOT}#g' /etc/apache2/apache2.conf

# Generate autoloader dasar SAJA (--no-scripts) saat build. Ini tidak butuh .env
# atau APP_KEY sama sekali, jadi aman dijalankan tanpa konteks aplikasi penuh.
# Optimisasi penuh (--optimize) dan hook Laravel (package:discover, config:cache,
# dst.) dijalankan oleh docker-entrypoint.sh saat container START, bukan saat
# build — di titik itu .env produksi sudah ter-inject oleh platform deploy.
RUN composer dump-autoload --no-scripts

# Copy & siapkan entrypoint script.
# sed menghapus carriage return (\r) untuk berjaga-jaga kalau file ini
# tersimpan dengan line-ending CRLF (umum terjadi di editor/Git Windows),
# karena #!/bin/sh akan gagal parse kalau ada \r tersisa di shebang line.
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
