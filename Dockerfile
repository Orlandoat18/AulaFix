
FROM php:8.3-apache

# Instalar la extensión PDO para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Activar el módulo rewrite de Apache
RUN a2enmod rewrite

# Configurar public como directorio raíz de la web
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
