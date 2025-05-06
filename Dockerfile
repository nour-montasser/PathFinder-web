# ────────────────────────────────────
# Stage 1: Composer builder
# ────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app

# Copy only the files Composer needs
COPY composer.json composer.lock ./

# Install all PHP dependencies (no‐dev, optimized)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# ────────────────────────────────────
# Stage 2: PHP + Apache final image
# ────────────────────────────────────
FROM php:8.2-apache

# 1) Install system libs & PHP extensions (including sodium)
RUN apt-get update && apt-get install -y \
      git unzip zip curl libicu-dev libonig-dev libxml2-dev \
      libzip-dev libpq-dev libpng-dev libjpeg-dev libfreetype6-dev \
      libsodium-dev pkg-config \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache sodium \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2) Enable mod_rewrite
RUN a2enmod rewrite

# 3) Set working dir
WORKDIR /var/www/html

# 4) Copy in the already-installed vendor/ and composer.json
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.json ./composer.json

# 5) Copy the rest of your application
COPY . .

# 6) Suppress Apache “ServerName” warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 7) Override your vhost (if you have one)
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# 8) Fix permissions
RUN chown -R www-data:www-data /var/www/html

# 9) Expose & start Apache
EXPOSE 80
CMD ["apache2-foreground"]
