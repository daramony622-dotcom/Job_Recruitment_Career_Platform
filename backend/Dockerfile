FROM richarvey/nginx-php-fpm:3.1.6

COPY . .

# Install Composer dependencies during the build
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