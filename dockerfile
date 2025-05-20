FROM php:8.2-apache

# Install required packages: cronolog for log rotation, and pdo_mysql for database access
RUN apt-get update && \
    apt-get install -y cronolog && \
    docker-php-ext-install pdo pdo_mysql && \
    a2enmod rewrite

# Create custom log directory for daily logs
RUN mkdir -p /var/log/custom-logs

# Copy Apache site configuration
COPY apache-site.conf /etc/apache2/sites-available/000-default.conf

# Copy custom PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/custom-php.ini

# Copy all application files to web root
COPY . /var/www/html

# (Optional) Set ownership for Apache user
RUN chown -R www-data:www-data /var/www/html
