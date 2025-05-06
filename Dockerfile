FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip zip curl libicu-dev libonig-dev libxml2-dev \
    libzip-dev libpq-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

RUN a2enmod rewrite

WORKDIR /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Copy and install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --verbose

# Then copy rest of the app
COPY . .

# Apache config
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
