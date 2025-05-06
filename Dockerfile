# 1) Base image
FROM php:8.2-apache

# 2) System & PHP extensions
RUN apt-get update && apt-get install -y \
      git unzip zip curl libicu-dev libonig-dev libxml2-dev \
      libzip-dev libpq-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3) Enable rewrite
RUN a2enmod rewrite

# 4) Set working dir
WORKDIR /var/www/html

# 5) Copy Composer files and install deps
COPY composer.json composer.lock ./
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 6) Copy the rest of your application
COPY . .

# 7) (Optional) Suppress Apache "ServerName" warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 8) Override vhost if needed
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# 9) Fix permissions
RUN chown -R www-data:www-data /var/www/html

# 10) Expose port 80 & start Apache
EXPOSE 80
CMD ["apache2-foreground"]
