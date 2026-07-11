# recetalia-site

Sitio web institucional de Recetalia (`recetalia.com`).

## Qué es

Sitio **PHP 7.4** casi estático. PHP se usa para:
- Includes de layout (`header.php`, `footer.php`).
- Formulario de contacto (`contact.php` → PHPMailer/SMTP en `mailer/`).
- Listado de farmacias habilitadas (`farmacias-habilitadas.php`).

**No usa base de datos.** Entry point: `index.php`.

Origen: extraído del Plesk de PROD (`143.110.212.167`, docroot `recetalia.com/public_html`) el 2026-07-10.

## Desarrollo local

```bash
docker build -t recetalia-site .
docker run --rm -p 8080:80 recetalia-site
# http://localhost:8080
```

## Deploy (pre-prod / validación)

Se despliega en el server nuevo (`159.203.26.217`, mismo que el pre nuevo).
Durante la validación se sirve **por IP en un puerto dedicado**; cuando pase a
prod se publicará en `recetalia.com`.

## Pendientes de hardening

- **Credencial SMTP hardcodeada** en `mailer/_email-sender.php` (usuario/clave de
  `notificaciones@doctorconsultas.com`). Rotar y externalizar a variable de entorno
  antes de pushear a un remoto público.
