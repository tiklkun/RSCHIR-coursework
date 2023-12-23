FROM php:7.4-apache

# Install required extensions
RUN docker-php-ext-install mysqli

WORKDIR /var/www/html/

# Copy only the necessary files for Composer installation
COPY composer.json composer.lock ./

# Install Composer
RUN apt-get update && \
    apt-get install -y unzip && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Temporary command to check directory contents
RUN ls -la /var/www/html


# Copy the remaining application files
COPY . .

EXPOSE 80
