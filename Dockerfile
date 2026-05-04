FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libpq-dev libzip-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo_pgsql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-scripts --no-interaction

COPY . .

RUN mkdir -p var/cache var/log var/share && chmod -R 777 var/

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
