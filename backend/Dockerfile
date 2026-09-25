FROM php:8.2-apache

# Install system dependencies and PHP extensions for PostgreSQL
RUN apt-get update && apt-get install -y \
     libpq-dev \
     libzip-dev \
     zip \
     unzip \
     git \
     curl \
     && docker-php-ext-install pdo pdo_pgsql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80
EXPOSE 80

# Run caching and migrations at runtime, then start Apache
CMD php artisan config:cache && php artisan route:cache && php artisan migrate --force && apache2-foreground