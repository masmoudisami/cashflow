FROM php:8.2-apache

# Installer dépendances système + Python
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Activer mod_rewrite (pour .htaccess)
RUN a2enmod rewrite

# Copier le projet
COPY . /var/www/html/

# Permissions (important pour exports/logs)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Installer dépendances Python si besoin
# (optionnel si tu as un requirements.txt)
# COPY requirements.txt .
# RUN pip3 install -r requirements.txt

EXPOSE 80