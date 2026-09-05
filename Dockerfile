# Sitio institucional Recetalia — PHP + includes (header/footer), formulario de
# contacto (PHPMailer/SMTP) y listado de farmacias habilitadas (mysqli → DB prod).
# Origen: Plesk de PROD (143.110.212.167), PHP 7.4.
# Imagen base parametrizable: PROD (.217, kernel 6.x) usa la default; el .98
# (CentOS 7, kernel 3.10, sin getrandom()) necesita la variante buster —
# el Apache de bullseye (APR 1.7) muere con "AH00141: Could not initialize
# random number generator" en loop. El override vive en docker-compose.dev98.yml.
ARG PHP_BASE=php:7.4-apache
FROM ${PHP_BASE}

# mod_rewrite por el .htaccess + extensión mysqli (farmacias-habilitadas.php
# consulta la DB de prod por SSL con un usuario read-only).
RUN a2enmod rewrite && docker-php-ext-install mysqli

# El contenido va directo al docroot de Apache
COPY . /var/www/html/

# HTTP interno; nginx (jonasal) hace de reverse proxy con TLS.
EXPOSE 80
