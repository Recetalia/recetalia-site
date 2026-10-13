<?php
/*
 * Filtro anti-spam de los formularios de contacto, sin servicios de terceros.
 *
 * El reCAPTCHA de las páginas sólo se chequeaba en el navegador: un bot que hace
 * POST directo a mailer/contact.php lo saltea. Esto se valida en el servidor:
 *  - campo trampa (`website`) invisible: una persona lo deja vacío;
 *  - ficha firmada con la hora en que se mostró la página (`ft`): rechaza envíos
 *    que no pasaron por la página, los de menos de 3 s y los de más de 24 h;
 *  - contenido: sin links y sin alfabetos que no usa nuestro público;
 *  - límite de 3 envíos por IP por hora.
 * Lo rechazado recibe la misma respuesta que un envío exitoso, para que el bot no
 * aprenda qué lo delató, y queda una línea en el log del contenedor.
 */

const ANTISPAM_MIN_SEG = 3;
const ANTISPAM_MAX_SEG = 86400;
const ANTISPAM_POR_HORA = 3;

function antispam_secreto() {
    $s = getenv('SITE_FORM_SECRET');
    if ($s) return $s;
    // Respaldo si falta la variable: un secreto local, que se pierde al recrear el contenedor.
    $f = sys_get_temp_dir() . '/recetalia-form-secret';
    if (!is_file($f)) file_put_contents($f, bin2hex(random_bytes(32)), LOCK_EX);
    return trim(file_get_contents($f));
}

function antispam_ficha() {
    $t = (string) time();
    return $t . '.' . hash_hmac('sha256', $t, antispam_secreto());
}

// Campos ocultos para poner dentro del <form>.
function antispam_campos() {
    return '<input type="hidden" name="ft" id="ft" value="' . antispam_ficha() . '">'
         . '<div style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true">'
         . '<label for="website">No completar</label>'
         . '<input type="text" name="website" id="website" tabindex="-1" autocomplete="off"></div>';
}

function antispam_ip() {
    return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '?';
}

// Devuelve null si el envío pasa, o el motivo del rechazo.
function antispam_motivo(array $campos, $exigirFicha = true) {
    if (trim($_POST['website'] ?? '') !== '') return 'campo-trampa';

    if ($exigirFicha) {
        $partes = explode('.', $_POST['ft'] ?? '', 2);
        if (count($partes) !== 2) return 'sin-ficha';
        [$t, $firma] = $partes;
        if (!ctype_digit($t) || !hash_equals(hash_hmac('sha256', $t, antispam_secreto()), $firma)) return 'ficha-invalida';
        $edad = time() - (int) $t;
        if ($edad < ANTISPAM_MIN_SEG) return 'demasiado-rapido';
        if ($edad > ANTISPAM_MAX_SEG) return 'ficha-vencida';
    }

    $texto = implode(' ', array_map('strval', $campos));
    if (preg_match('~(https?://|www\.|<a\s|\[url)~i', $texto)) return 'link';
    if (preg_match('/[\p{Cyrillic}\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}\p{Arabic}\p{Thai}]/u', $texto)) return 'alfabeto';

    $dir = sys_get_temp_dir() . '/recetalia-form-rl';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $f = $dir . '/' . hash('sha256', antispam_ip());
    $ahora = time();
    $previos = is_file($f) ? array_filter(array_map('intval', file($f, FILE_IGNORE_NEW_LINES)), fn($x) => $x > $ahora - 3600) : [];
    if (count($previos) >= ANTISPAM_POR_HORA) return 'limite-ip';
    $previos[] = $ahora;
    file_put_contents($f, implode("\n", $previos), LOCK_EX);

    return null;
}

// Corta el pedido si es spam, respondiendo como si se hubiera enviado.
function antispam_cortar_si_spam(array $campos, $exigirFicha = true) {
    $motivo = antispam_motivo($campos, $exigirFicha);
    if ($motivo === null) return;
    error_log('antispam: descartado motivo=' . $motivo . ' form=' . ($_SERVER['SCRIPT_NAME'] ?? '?'));
    echo "<div class='alert alert-success'><h3>Email enviado Exitosamente.</h3><p>Su mensaje ha sido enviado exitosamente.</p></div>";
    exit();
}

// Escapa lo que escribió el visitante antes de meterlo en el HTML del mail.
function antispam_html($s) {
    return nl2br(htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'));
}
