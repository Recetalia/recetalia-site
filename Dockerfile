# Sitio institucional Recetalia — PHP estático + includes (header/footer),
# formulario de contacto (PHPMailer/SMTP) y listado de farmacias. Sin base de datos.
# Origen: Plesk de PROD (143.110.212.167), PHP 7.4.
FROM php:7.4-apache

# mod_rewrite por si el .htaccess lo usa; el resto del sitio es estático + mail()
RUN a2enmod rewrite

# El contenido va directo al docroot de Apache
COPY . /var/www/html/

# Apache escucha 80 dentro del contenedor; el compose/host publica el puerto.
EXPOSE 80
