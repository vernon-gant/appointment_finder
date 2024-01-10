# Use PHP FPM image
FROM php:8.1-fpm

# Set up the working directory
WORKDIR /var/www/html

# Copy the application code to the container
COPY . /var/www/html

# Install system dependencies
# Install required PHP extensions (pdo, pdo_mysql, and mbstring)
RUN apt-get update && apt-get install -y \
        libonig-dev \
        libzip-dev \
        zip \
    && docker-php-ext-install pdo pdo_mysql mbstring

# Clear out the local repository of retrieved package files
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

#Print working directory
RUN pwd

# Install Composer dependencies with the --no-dev flag to skip development dependencies
RUN composer install --no-dev --optimize-autoloader

# Set proper permissions on the application directory
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Start PHP-FPM
CMD ["php-fpm"]