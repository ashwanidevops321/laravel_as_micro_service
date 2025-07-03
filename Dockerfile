# Stage 1: Composer dependencies
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-progress --no-interaction

# Stage 2: Node build
FROM node:20.11.1-alpine AS node
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install --no-audit --no-fund --prefer-offline

COPY . .
RUN npm run build

# Stage 3: Production PHP-FPM
FROM php:8.3-fpm-alpine
WORKDIR /app

# Install PHP extensions
RUN apk add --no-cache \
    git \
    libfreetype6-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    unzip \
    zip && \
    docker-php-ext-configure gd \
    --with-freetype=/usr/include/ \
    --with-jpeg=/usr/include/ \
    --with-webp=/usr/include/ && \
    docker-php-ext-install \
    pdo_mysql \
    gd \
    zip

# Copy application code
COPY . .

# Copy built vendor and assets
COPY --from=vendor /app/vendor ./vendor
COPY --from=node /app/public ./public

# Fix permissions only for necessary directories
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 755 /app/storage /app/bootstrap/cache && \
    rm -rf /root/.composer /root/.cache

EXPOSE 9000
CMD ["php-fpm"]
