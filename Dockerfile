# 1. Start from an official base image
# This gives us PHP 8.2 and an Apache web server
FROM php:8.2-apache

# 2. Install the necessary PHP extensions
# This is the most important part for connecting to your Neon (PostgreSQL) database.
RUN docker-php-ext-install pdo pdo_pgsql

# 3. Enable Apache's 'mod_rewrite'
# This is good practice and allows for "clean URLs" if you add them later.
RUN a2enmod rewrite

# 4. Copy all your project files into the web server's root directory
# The default directory is /var/www/html
COPY . /var/www/html/

# 5. Ensure Apache has the correct permissions (good practice)
RUN chown -R www-data:www-data /var/www/html