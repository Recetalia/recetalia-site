<?php
// <head> y scripts comunes de las páginas del vademécum. Sin <base href>: en PRE mandaría los links
// a PROD. Todas las rutas son absolutas desde la raíz.
require_once __DIR__ . '/vademecum.php';
require_once __DIR__ . '/layout.php';

// Canonical, Open Graph y sitemap apuntan siempre al dominio público (no al Host del pedido, que
// lo elige el cliente). SITE_PUBLIC_URL permite otro dominio sin tocar código.
function vd_url_absoluta($ruta) {
    return rtrim(getenv('SITE_PUBLIC_URL') ?: 'https://recetalia.com', '/') . $ruta;
}

// $meta: title, description, canonical (ruta), og_type, jsonld (array|null), robots (opcional)
// Delega en el layout común del sitio (inc/layout.php): header, footer y css/rc.css compartidos.
function vd_head(array $meta) {
    rc_head(array(
        'title' => $meta['title'],
        'description' => $meta['description'],
        'canonical' => vd_url_absoluta($meta['canonical']),
        'og_type' => isset($meta['og_type']) ? $meta['og_type'] : 'website',
        'robots' => isset($meta['robots']) ? $meta['robots'] : null,
        'jsonld' => isset($meta['jsonld']) ? $meta['jsonld'] : null,
        'css' => array('/css/vademecum.css?v=5'),
        'body_clase' => 'vd',
    ));
}

function vd_pie($scriptsExtra = '') {
    rc_pie('<script src="/js/vademecum.js?v=3" defer></script>' . $scriptsExtra);
}

// Aviso legal que va en todas las páginas del vademécum.
const VD_AVISO = 'Información de referencia para profesionales de la salud. No reemplaza la indicación ni el consejo de su médico o químico farmacéutico.';
