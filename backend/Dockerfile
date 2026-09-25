FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
     libpq-dev \
     libzip-dev \
     zip \
     unzip \
     git \
     curl \
     && docker-php-ext-install pdo pdo_pgsql zip \
     && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Allow Composer to run as root (THE MISSING PIECE)
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80
EXPOSE 80

# Run migrations, cache config/routes, and start Apache
CMD php artisan migrate --force && php artisan config:cache && php artisan route:cache && apache2-foreground