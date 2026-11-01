# Stage 1: i-build ang CSS/JS (Vite + Tailwind)
FROM node:20 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: PHP/Laravel
FROM php:8.3-cli

RUN apt-get update && apt-get install -y git unzip libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Kuhaa ang built CSS/JS gikan sa stage 1
COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000
CMD php artisan migrate --force; php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
