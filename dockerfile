# ============================================
# PHP + Apache Server for Render Deployment
# ============================================

FROM php:8.1-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install required PHP Extensions
RUN docker-php-ext-install mysqli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project into container
COPY . /var/www/html/

# Set document root (optional but clean)
WORKDIR /var/www/html/

# Install PHP dependencies (Pusher)
RUN composer require pusher/pusher-php-server

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Render exposes PORT env variable — Apache must listen to it.
# Replace the default 80 with the Render-provided port.
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf

# Start Apache
CMD ["apache2-foreground"]
