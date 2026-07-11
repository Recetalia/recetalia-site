# Sitio institucional Recetalia — PHP + includes (header/footer), formulario de
# contacto (PHPMailer/SMTP) y listado de farmacias habilitadas (mysqli → DB prod).
# Origen: Plesk de PROD (143.110.212.167), PHP 7.4.
FROM php:7.4-apache

# mod_rewrite por el .htaccess + extensión mysqli (farmacias-habilitadas.php
# consulta la DB de prod por SSL con un usuario read-only).
RUN a2enmod rewrite && docker-php-ext-install mysqli

# El contenido va directo al docroot de Apache
COPY . /var/www/html/

# HTTP interno; nginx (jonasal) hace de reverse proxy con TLS.
EXPOSE 80
