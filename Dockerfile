# Sitio institucional Recetalia — PHP estático + includes (header/footer),
# formulario de contacto (PHPMailer/SMTP) y listado de farmacias. Sin base de datos.
# Origen: Plesk de PROD (143.110.212.167), PHP 7.4.
FROM php:7.4-apache

# mod_rewrite (por el .htaccess) + ssl (HTTPS servido por el propio contenedor).
# Durante la validación por IP el contenedor termina TLS con un cert self-signed;
# en prod detrás de Cloudflare/nginx se puede seguir usando HTTP interno.
RUN a2enmod rewrite ssl && a2ensite default-ssl

# Cert self-signed para servir HTTPS por IP (validación). Reemplazable en runtime
# montando cert/clave reales en /etc/ssl/private/site.key y /etc/ssl/certs/site.crt.
RUN openssl req -x509 -nodes -newkey rsa:2048 -days 825 \
      -keyout /etc/ssl/private/ssl-cert-snakeoil.key \
      -out /etc/ssl/certs/ssl-cert-snakeoil.pem \
      -subj "/CN=recetalia.com/O=Recetalia" 2>/dev/null

# El contenido va directo al docroot de Apache
COPY . /var/www/html/

# 80 (HTTP) y 443 (HTTPS) dentro del contenedor; el host publica los puertos.
EXPOSE 80 443
