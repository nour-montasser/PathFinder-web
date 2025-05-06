FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip curl libicu-dev libonig-dev libxml2-dev \
    libzip-dev libpq-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache \
    && apt-get clean

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy only composer files first to leverage caching
COPY composer.json composer.lock ./

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Install PHP dependencies (no-dev)
RUN composer install --no-dev --optimize-autoloader || cat /var/www/html/composer.lock

# Copy rest of the app
COPY . .

# Apache config override
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Set file ownership to Apache user
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
