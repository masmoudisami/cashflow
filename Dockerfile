FROM php:8.1-apache

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql mysqli calendar

# Activer Apache modules
RUN a2enmod rewrite

# Configuration PHP
RUN echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/uploads.ini

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . /var/www/html/cashflow/

# Créer le dossier exports AVANT de modifier les permissions
RUN mkdir -p /var/www/html/cashflow/exports

# Permissions - dans le bon ordre
RUN chown -R www-data:www-data /var/www/html/cashflow/ \
    && chmod -R 755 /var/www/html/cashflow/ \
    && chmod 777 /var/www/html/cashflow/exports

# Configuration Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Ports
EXPOSE 80

# Démarrer Apache
CMD ["apache2-foreground"]