# Stage 1: Composer dependencies
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-progress --no-interaction

# Stage 2: Node build
FROM node:20.11.1-alpine AS node
WORKDIR /app

# ✅ Copy only what's needed for npm install
COPY package.json package-lock.json ./
RUN npm install --no-audit --no-fund --prefer-offline

# ✅ Now copy the full source (after node_modules created)
COPY . .

# ✅ Build frontend (Vite, Laravel Mix, etc.)
RUN npm run build

# Stage 3: Production PHP-FPM
FROM php:8.3-fpm-alpine
WORKDIR /app

# PHP extensions
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

# Copy application source
COPY . .

# ✅ Copy vendor and built assets
COPY --from=vendor /app/vendor ./vendor
COPY --from=node /app/public ./public

# Permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 755 /app/storage /app/bootstrap/cache && \
    rm -rf /root/.composer /root/.cache

EXPOSE 9000
CMD ["php-fpm"]
