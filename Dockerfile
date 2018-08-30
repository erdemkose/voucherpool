FROM php:7.2-apache

# Application folders
ENV VOUCHERPOOL_ROOT /var/www/voucherpool
ENV VOUCHERPOOL_DOCUMENT_ROOT ${VOUCHERPOOL_ROOT}/public
ENV COMPOSER_ALLOW_SUPERUSER 1

# Prepare the image
# Composer requires either git or unzip or php-zip to download and install the packages
RUN apt update -y \
 && apt install unzip -y

# Copy composer executable from image
COPY --from=composer:1.7.2 /usr/bin/composer /usr/bin/composer

# Prepare Apache
# Modify virtual host file to point to the project folder
RUN sed -ri -e "s!/var/www/html!${VOUCHERPOOL_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf
# Slim framework requires mod_rewrite to function properly
RUN a2enmod rewrite

# Copy project files
COPY . ${VOUCHERPOOL_ROOT}
WORKDIR ${VOUCHERPOOL_ROOT}

# Install the project using composer
RUN composer install --no-progress --no-suggest --optimize-autoloader
RUN composer test

# We need to make sure db and logs folders are web writable
RUN chown -R www-data:www-data ${VOUCHERPOOL_ROOT}/db ${VOUCHERPOOL_ROOT}/logs

# Restart Apache to load the new configuration file and modules
RUN /etc/init.d/apache2 restart
