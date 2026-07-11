# Sitio institucional Recetalia — PHP estático + includes (header/footer),
# formulario de contacto (PHPMailer/SMTP) y listado de farmacias. Sin base de datos.
# Origen: Plesk de PROD (143.110.212.167), PHP 7.4.
FROM php:7.4-apache

# mod_rewrite por el .htaccess. El TLS lo termina el nginx del stack (igual que
# los frontends): este contenedor sirve HTTP interno en el puerto 80.
RUN a2enmod rewrite

# El contenido va directo al docroot de Apache
COPY . /var/www/html/

# HTTP interno; nginx (jonasal) hace de reverse proxy con TLS.
EXPOSE 80
