# Sitio institucional Recetalia — PHP + includes (header/footer), formulario de
# contacto (PHPMailer/SMTP) y listado de farmacias habilitadas (PDO pgsql → DB prod).
# Origen: Plesk de PROD (143.110.212.167), PHP 7.4.
# Imagen base parametrizable: PROD (.217, kernel 6.x) usa la default; el .98
# (CentOS 7, kernel 3.10, sin getrandom()) necesita la variante buster —
# el Apache de bullseye (APR 1.7) muere con "AH00141: Could not initialize
# random number generator" en loop. El override vive en docker-compose.dev98.yml.
# Las dos son php:7.4-apache(-buster) recompiladas con el arreglo de PHP2Shell
# (CVE-2026-109618/109619); receta en deploy-recetalia/php-parcheado/.
ARG PHP_BASE=registrypre.recetadigital.uy/recetalia/php-base:7.4-apache-php2shell
FROM ${PHP_BASE}

# mod_rewrite por el .htaccess + pdo_pgsql (farmacias-habilitadas.php consulta la DB
# PostgreSQL de prod por SSL con un usuario read-only).
# bullseye terminó su LTS y debian-security devuelve 404 para libpq-dev (medido 2026-10-30): se instala
# desde el repo principal. Sólo hacen falta los headers para compilar pdo_pgsql.
RUN a2enmod rewrite \
 && sed -i '/debian-security/d' /etc/apt/sources.list \
 && apt-get update && apt-get install -y --no-install-recommends libpq-dev \
 && docker-php-ext-install pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

# El contenido va directo al docroot de Apache
COPY . /var/www/html/

# HTTP interno; nginx (jonasal) hace de reverse proxy con TLS.
EXPOSE 80
