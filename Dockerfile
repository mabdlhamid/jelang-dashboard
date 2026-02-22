FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip zip \
    libzip-dev libicu-dev libpng-dev \
    && docker-php-ext-install intl zip gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --ignore-platform-reqs --no-interaction --optimize-autoloader

CMD php artisan serve --host=0.0.0.0 --port=${PORT}