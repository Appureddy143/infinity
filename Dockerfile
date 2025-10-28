# 1. Start from an official base image
# This gives us PHP 8.2 and an Apache web server
FROM php:8.2-apache

# 2. Install system dependencies
# THIS IS THE NEW LINE THAT FIXES THE ERROR
# We are installing the 'libpq-dev' package, which contains the 'libpq-fe.h' file.
RUN apt-get update && apt-get install -y libpq-dev && rm -rf /var/lib/apt/lists/*

# 3. Install the necessary PHP extensions
# Now this command will find the files it needs and succeed.
RUN docker-php-ext-install pdo pdo_pgsql

# 4. Enable Apache's 'mod_rewrite'
# This is good practice and allows for "clean URLs" if you add them later.
RUN a2enmod rewrite

# 5. Copy all your project files into the web server's root directory
# The default directory is /var/www/html
COPY . /var/www/html/

# 6. Ensure Apache has the correct permissions (good practice)
RUN chown -R www-data:www-data /var/www/html