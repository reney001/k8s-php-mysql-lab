FROM php:8.2-apache

# Install mysqli extension for MySQL connectivity
RUN docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite (handy for clean URLs later)
RUN a2enmod rewrite

# Copy application source into Apache's document root
COPY app/ /var/www/html/

# Ensure correct ownership/permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
