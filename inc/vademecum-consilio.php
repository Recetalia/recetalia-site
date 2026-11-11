<?php
// Ficha técnica de referencia desde Consilio (GET /monografia), con caché de archivo.
// La caché es obligatoria: el Consilio de PROD es chico y el rastreo de miles de páginas lo
// saturaría. Si Consilio no responde se sirve la caché aunque esté vencida.
require_once __DIR__ . '/vademecum.php';

const VD_CACHE_TTL = 86400; // 24 h

function vd_clave_cache(array $sustancias) {
    $n = array_map('fh_normalizar', $sustancias);
    sort($n);
    return sha1(implode('+', $n));
}

function vd_cache_dir() {
    return getenv('SITE_VADEMECUM_CACHE_DIR') ?: sys_get_temp_dir() . '/vademecum-cache';
}

// null si no existe; si no, ['data' => ..., 'vencida' => bool].
function vd_cache_leer($dir, $clave, $ttl) {
    $f = $dir . '/' . $clave . '.json';
    clearstatcache(true, $f); // PHP 7.4 cachea filemtime dentro del mismo proceso
    if (!is_file($f)) {
        return null;
    }
    $data = json_decode((string)file_get_contents($f), true);
    if (!is_array($data)) {
        return null;
    }
    return array('data' => $data, 'vencida' => (time() - filemtime($f)) > $ttl);
}

function vd_cache_escribir($dir, $clave, array $data) {
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
        return;
    }
    $tmp = $dir . '/' . $clave . '.' . getmypid() . '.tmp';
    if (@file_put_contents($tmp, json_encode($data)) !== false) {
        @rename($tmp, $dir . '/' . $clave . '.json');
    }
}

// Sólo lee la caché (vigente o vencida); no llama a Consilio. Para el buscador.
function vd_ficha_en_cache(array $sustancias) {
    $c = vd_cache_leer(vd_cache_dir(), vd_clave_cache($sustancias), VD_CACHE_TTL);
    return $c ? $c['data'] : null;
}

function vd_consilio_pedir(array $sustancias) {
    $base = rtrim((string)getenv('SITE_CONSILIO_URL'), '/');
    if ($base === '' || !$sustancias) {
        return null;
    }
    $ch = curl_init($base . '/monografia?' . http_build_query(array('sustancias' => implode(',', $sustancias))));
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => array('X-API-Key: ' . getenv('SITE_CONSILIO_API_KEY'), 'Accept: application/json'),
    ));
    $cuerpo = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (PHP_VERSION_ID < 80000) { curl_close($ch); } // no-op desde 8.0, deprecada en 8.5
    if ($cuerpo === false || $codigo !== 200) {
        error_log("vademecum: Consilio respondió $codigo");
        return null;
    }
    $data = json_decode($cuerpo, true);
    if (!is_array($data) || !array_key_exists('disponible', $data)) {
        return null;
    }
    // "sin_datos" = la base de Consilio todavía no tiene fichas cargadas: no es "este producto no tiene ficha",
    // y no debe quedar 24 h en caché como si lo fuera.
    return (isset($data['motivo']) && $data['motivo'] === 'sin_datos') ? null : $data;
}

// ['estado' => 'ok'|'sin_ficha'|'no_disponible', 'data' => array|null]
function vd_ficha(array $sustancias) {
    $dir = vd_cache_dir();
    $clave = vd_clave_cache($sustancias);
    $cache = vd_cache_leer($dir, $clave, VD_CACHE_TTL);
    $data = ($cache && !$cache['vencida']) ? $cache['data'] : null;
    if ($data === null) {
        $nueva = vd_consilio_pedir($sustancias);
        if ($nueva !== null) {
            vd_cache_escribir($dir, $clave, $nueva);
            $data = $nueva;
        } elseif ($cache) {
            $data = $cache['data'];
        }
    }
    if ($data === null) {
        return array('estado' => 'no_disponible', 'data' => null);
    }
    return array('estado' => !empty($data['disponible']) ? 'ok' : 'sin_ficha', 'data' => $data);
}
