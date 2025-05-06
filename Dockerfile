FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip curl libicu-dev libonig-dev libxml2-dev \
    libzip-dev libpq-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

RUN a2enmod rewrite

WORKDIR /var/www/html

# Composer install (optimized)
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Copy rest of the app
COPY . .

# Override Apache config
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Permissions (optional for render)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
