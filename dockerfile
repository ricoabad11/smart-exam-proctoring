# Use official PHP with Apache
FROM php:8.2-apache

# Install required PHP extensions
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite (optional but good)
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project into container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Apache will automatically serve index.php
EXPOSE 80
