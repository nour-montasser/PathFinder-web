# ────────────────────────────────────────────────────
# Stage 1: Build all PHP dependencies with php-cli
# ────────────────────────────────────────────────────
FROM php:8.2-cli AS builder

# Install system libs & PHP extensions needed by your app
RUN apt-get update && apt-get install -y \
      git unzip zip curl libicu-dev libonig-dev libxml2-dev \
      libzip-dev libpq-dev libpng-dev libjpeg-dev libfreetype6-dev \
      libsodium-dev pkg-config \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache sodium \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy only the files needed for composer install
COPY composer.json composer.lock ./

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Run composer (this time inside a fully-featured PHP environment)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# ────────────────────────────────────────────────────
# Stage 2: Final image with Apache + PHP-FPM
# ────────────────────────────────────────────────────
FROM php:8.2-apache

# Enable rewrite
RUN a2enmod rewrite

# Set working dir
WORKDIR /var/www/html

# Copy built vendor/ and composer.json from builder
COPY --from=builder /app/vendor ./vendor
COPY --from=builder /app/composer.json ./composer.json

# Copy the rest of the application
COPY . .

# Suppress Apache ServerName warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Override vhost if you have one
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# Expose & start Apache
EXPOSE 80
CMD ["apache2-foreground"]
