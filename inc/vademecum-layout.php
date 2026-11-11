<?php
// <head> y scripts comunes de las páginas del vademécum. Sin <base href>: en PRE mandaría los links
// a PROD. Todas las rutas son absolutas desde la raíz.
require_once __DIR__ . '/vademecum.php';

// Canonical, Open Graph y sitemap apuntan siempre al dominio público (no al Host del pedido, que
// lo elige el cliente). SITE_PUBLIC_URL permite otro dominio sin tocar código.
function vd_url_absoluta($ruta) {
    return rtrim(getenv('SITE_PUBLIC_URL') ?: 'https://recetalia.com', '/') . $ruta;
}

// $meta: title, description, canonical (ruta), og_type, jsonld (array|null), robots (opcional)
function vd_head(array $meta) {
    $canonical = vd_url_absoluta($meta['canonical']);
    ?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title><?php echo vd_e($meta['title']); ?></title>
    <meta name="description" content="<?php echo vd_e($meta['description']); ?>" />
    <meta name="author" content="Recetalia" />
    <?php if (!empty($meta['robots'])): ?><meta name="robots" content="<?php echo vd_e($meta['robots']); ?>" /><?php endif; ?>
    <link rel="canonical" href="<?php echo vd_e($canonical); ?>" />
    <meta property="og:site_name" content="Recetalia" />
    <meta property="og:type" content="<?php echo vd_e(isset($meta['og_type']) ? $meta['og_type'] : 'website'); ?>" />
    <meta property="og:title" content="<?php echo vd_e($meta['title']); ?>" />
    <meta property="og:description" content="<?php echo vd_e($meta['description']); ?>" />
    <meta property="og:url" content="<?php echo vd_e($canonical); ?>" />
    <meta property="og:locale" content="es_UY" />
    <link rel="icon" href="/images/favicon-recetalia.png" type="image/png">
    <link rel="stylesheet" href="/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/css/slimmenu.min.css" />
    <link rel="stylesheet" href="/styles.css" />
    <link rel="stylesheet" href="/css/responsive.css" />
    <link rel="stylesheet" href="/css/vademecum.css?v=2" />
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,700&display=swap" rel="stylesheet">
    <?php if (!empty($meta['jsonld'])): ?>
    <script type="application/ld+json"><?php echo json_encode($meta['jsonld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG); ?></script>
    <?php endif; ?>
</head>
<body class="vd">
    <a class="vd-saltar" href="#contenido">Saltar al contenido</a>
    <?php
    include __DIR__ . '/../header.php';
}

function vd_pie($scriptsExtra = '') {
    include __DIR__ . '/../footer.php';
    ?>
    <script src="/js/jquery-3.2.1.min.js"></script>
    <script src="/js/popper.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/jquery.slimmenu.min.js"></script>
    <script src="/js/vademecum.js?v=2" defer></script>
    <?php echo $scriptsExtra; ?>
</body>
</html>
    <?php
}

// Aviso legal que va en todas las páginas del vademécum.
const VD_AVISO = 'Información de referencia para profesionales de la salud. No reemplaza la indicación ni el consejo de su médico o químico farmacéutico.';
