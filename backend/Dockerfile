FROM richarvey/nginx-php-fpm:3.1.6

# Install PostgreSQL PHP extension
RUN apk add --no-cache postgresql-dev \
     && docker-php-ext-install pdo_pgsql pgsql

# Set the working directory
WORKDIR /var/www/html

# Copy the application code
COPY . .

# Install Composer dependencies (THIS IS THE MISSING PIECE)
RUN composer install --no-dev --optimize-autoloader

# Make the deploy script executable
RUN chmod +x /var/www/html/scripts/00-laravel-deploy.sh

# Image config
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

CMD ["/start.sh"]