<?php
// Layout común del sitio: <head>, header, footer y scripts. Lo usan todas las páginas (también el
// vademécum, vía vd_head/vd_pie). Sin <base href>: en PRE mandaría los links a PROD; todas las rutas
// son absolutas desde la raíz. Sistema de diseño: design-system/recetalia/MASTER.md.

function rc_e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Íconos Lucide (trazo 2, 24×24), inline para no depender de una fuente de íconos.
function rc_icono($nombre, $clase = 'rc-icono') {
    static $paths = array(
        'menu' => '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
        'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'map-pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'stethoscope' => '<path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/><path d="M8 15v1a6 6 0 0 0 6 6a6 6 0 0 0 6-6v-4"/><circle cx="20" cy="10" r="2"/>',
        'store' => '<path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/>',
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'building' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
        'pill' => '<path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/>',
        'file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
        'smartphone' => '<rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/>',
        'shield-check' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'external' => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>',
        'log-in' => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/>',
        'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
        'alert' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        'heart-pulse' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/>',
        'list-check' => '<path d="M11 18H3"/><path d="m15 18 2 2 4-4"/><path d="M16 12H3"/><path d="M16 6H3"/>',
    );
    if (!isset($paths[$nombre])) return '';
    return '<svg class="' . rc_e($clase) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[$nombre] . '</svg>';
}

// Sección del sitio para marcar el ítem activo del menú.
function rc_seccion() {
    $s = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    if ($s === '/index.php' || $s === '/') return 'inicio';
    if (strpos($s, '/medicamentos/') === 0) return 'medicamentos';
    if ($s === '/farmacias-habilitadas.php') return 'farmacias';
    return '';
}

// $meta: title, description, canonical (URL absoluta, opcional), og_type, robots, jsonld (array),
//        css (array de hojas extra), body_clase, recaptcha (bool), head_extra (HTML).
function rc_head(array $meta) {
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo rc_e($meta['title']); ?></title>
    <meta name="description" content="<?php echo rc_e($meta['description']); ?>" />
    <meta name="author" content="Recetalia" />
    <meta name="theme-color" content="#0B7A88" />
    <?php if (!empty($meta['robots'])): ?><meta name="robots" content="<?php echo rc_e($meta['robots']); ?>" /><?php endif; ?>
    <?php if (!empty($meta['canonical'])): ?>
    <link rel="canonical" href="<?php echo rc_e($meta['canonical']); ?>" />
    <meta property="og:url" content="<?php echo rc_e($meta['canonical']); ?>" />
    <?php endif; ?>
    <meta property="og:site_name" content="Recetalia" />
    <meta property="og:type" content="<?php echo rc_e(isset($meta['og_type']) ? $meta['og_type'] : 'website'); ?>" />
    <meta property="og:title" content="<?php echo rc_e($meta['title']); ?>" />
    <meta property="og:description" content="<?php echo rc_e($meta['description']); ?>" />
    <meta property="og:locale" content="es_UY" />
    <link rel="icon" href="/images/favicon-recetalia.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&display=swap">
    <link rel="stylesheet" href="/css/rc.css?v=2" />
    <?php if (!empty($meta['css'])): foreach ($meta['css'] as $hoja): ?>
    <link rel="stylesheet" href="<?php echo rc_e($hoja); ?>" />
    <?php endforeach; endif; ?>
    <script>document.documentElement.className += ' rc-js';</script>
    <?php if (!empty($meta['jsonld'])): ?>
    <script type="application/ld+json"><?php echo json_encode($meta['jsonld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG); ?></script>
    <?php endif; ?>
    <?php if (!empty($meta['recaptcha'])): ?>
    <script>
    var onloadCallback = function () {
        grecaptcha.render('html_element', { 'sitekey': '6LdnY14aAAAAANJNq-fqC7aUT2lrU9C4jZvKsete' });
    };
    </script>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=es" async defer></script>
    <?php endif; ?>
    <?php if (!empty($meta['head_extra'])) echo $meta['head_extra']; ?>
</head>
<body class="rc<?php echo !empty($meta['body_clase']) ? ' ' . rc_e($meta['body_clase']) : ''; ?>">
    <a class="rc-saltar" href="#contenido">Saltar al contenido</a>
    <?php
    include __DIR__ . '/../header.php';
}

// $chat: widget de videoconsulta de IWTG (estaba en la portada y las páginas institucionales).
function rc_pie($scriptsExtra = '', $chat = false) {
    include __DIR__ . '/../footer.php';
    ?>
    <script src="/js/rc.js?v=2" defer></script>
    <?php echo $scriptsExtra; ?>
    <?php if ($chat): ?>
    <script src="https://videoconsulta.iwtg.com/video-chat.umd.min.js" defer></script>
    <link rel="stylesheet" href="https://videoconsulta.iwtg.com/video-chat.css">
    <video-chat bottom right text-color="#ffffff" primary-color="#0B7A88" public-key="QXfeToaWrocXmZosEiwJlXcoy"></video-chat>
    <?php endif; ?>
</body>
</html>
    <?php
}

// Formulario de contacto (portada y tutorial). Contrato con mailer/contact.php: POST de name, email,
// comment, ft y website (inc/antispam.php); el envío por AJAX lo hace js/rc.js (.about-contact-form).
function rc_contacto() {
    require_once __DIR__ . '/antispam.php';
    ?>
    <section class="rc-seccion rc-contacto" id="ContactSet" aria-labelledby="contacto-titulo">
        <div class="rc-container rc-contacto-grilla">
            <div class="rc-contacto-texto">
                <h2 id="contacto-titulo">Contacto</h2>
                <p class="rc-bajada">Envianos tu mensaje y te contestaremos a la brevedad.</p>
                <ul class="rc-contacto-datos">
                    <li><?php echo rc_icono('mail'); ?><a href="mailto:hello@recetalia.com">hello@recetalia.com</a></li>
                    <li><?php echo rc_icono('map-pin'); ?><span>WTC Montevideo, Torre III, Piso 12.</span></li>
                </ul>
            </div>
            <form class="rc-tarjeta rc-form about-contact-form" action="/mailer/contact.php" method="POST">
                <?php echo antispam_campos(); ?>
                <div class="rc-form-fila">
                    <div class="rc-campo">
                        <label class="rc-label" for="name">Nombre completo</label>
                        <input class="rc-input" type="text" name="name" id="name" autocomplete="name" required maxlength="120">
                    </div>
                    <div class="rc-campo">
                        <label class="rc-label" for="email">Email</label>
                        <input class="rc-input" type="email" name="email" id="email" autocomplete="email" required maxlength="200">
                    </div>
                </div>
                <div class="rc-campo">
                    <label class="rc-label" for="comment">Mensaje</label>
                    <textarea class="rc-input" rows="5" name="comment" id="comment" maxlength="4000"></textarea>
                </div>
                <div class="rc-campo rc-captcha"><div id="html_element"></div></div>
                <button type="submit" class="rc-btn rc-btn-primario" id="submit"><?php echo rc_icono('send'); ?>Enviar mensaje</button>
                <div id="message" class="rc-form-mensaje" role="status" aria-live="polite"></div>
            </form>
        </div>
    </section>
    <?php
}
