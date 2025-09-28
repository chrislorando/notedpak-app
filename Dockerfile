# Stage 1: frontend build
FROM node:20 AS frontend
WORKDIR /app

# Copy package.json + lock
COPY package*.json ./
RUN npm install

# Copy seluruh project (dibutuhkan untuk build asset frontend)
COPY . .
RUN npm run build


# Stage 2: composer dependencies
FROM composer:2 AS vendor
WORKDIR /app

# Gunakan composer.server.json + lock
COPY composer.server.json composer.json
COPY composer.server.lock composer.lock

RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

# Copy source supaya autoload bisa dioptimasi
COPY . .
RUN composer dump-autoload --optimize --no-scripts


# Stage 3: final php-fpm
FROM php:8.3-fpm

# Install system dependencies + php extensions
RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    git \
    libzip-dev \
    libxml2-dev \
    libsqlite3-dev \
    libgmp-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_sqlite zip pcntl bcmath gmp gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Copy vendor dari stage composer
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.json ./composer.json
COPY --from=vendor /app/composer.lock ./composer.lock

# Copy frontend build dari stage node
COPY --from=frontend /app/public/build ./public/build

# Copy seluruh aplikasi (kecuali node_modules/vendor yang sudah ada)
COPY . .

# Persiapan storage + database sqlite
RUN mkdir -p storage/framework/{cache,sessions,testing,views} \
    && mkdir -p database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database \
    && chmod 664 database/database.sqlite

EXPOSE 9000
CMD ["php-fpm"]
