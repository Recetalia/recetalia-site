<?php
// Consultas del vademécum sobre el catálogo DNMA (PostgreSQL, schema `dnma`), con el rol read-only
// del sitio (SITE_DB_*; SELECT sobre amp, vmp, vmp_sustancia, sustancia, laboratorio).
// Todos los ids del DNMA son varchar: se tratan siempre como string.
require_once __DIR__ . '/vademecum.php';

// translate() en lugar de unaccent (la extensión no está instalada): misma regla que fh_normalizar.
const VD_SQL_NORM_DESDE = 'áéíóúüñàèìòù';
const VD_SQL_NORM_HACIA = 'aeiouunaeiou';

function vd_pdo() {
    $dsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s;sslmode=require',
        getenv('SITE_DB_HOST'),
        (int)(getenv('SITE_DB_PORT') ?: 25060),
        getenv('SITE_DB_NAME') ?: 'recetalia'
    );
    return new PDO($dsn, getenv('SITE_DB_USER'), getenv('SITE_DB_PASS'), array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ));
}

function vd_norm_sql($col) {
    return "translate(lower($col), '" . VD_SQL_NORM_DESDE . "', '" . VD_SQL_NORM_HACIA . "')";
}

// Producto con su genérico y laboratorio; null si no existe.
function vd_producto(PDO $pdo, $ampId) {
    $st = $pdo->prepare("
        SELECT a.amp_id, a.amp_dsc, a.nro_msp, a.comercializado, a.vmp_id,
               v.vmp_dsc, v.condvta_id, l.nombre AS laboratorio
        FROM dnma.amp a
        JOIN dnma.vmp v ON v.vmp_id = a.vmp_id
        LEFT JOIN dnma.laboratorio l ON l.lab_id = a.laboratorio_id
        WHERE a.amp_id = :id");
    $st->execute(array('id' => (string)$ampId));
    $fila = $st->fetch();
    return $fila ?: null;
}

// Nombres de las sustancias del genérico, en el orden del DNMA.
function vd_sustancias(PDO $pdo, $vmpId) {
    $st = $pdo->prepare("
        SELECT s.sustancia_dsc
        FROM dnma.vmp_sustancia vs
        JOIN dnma.sustancia s ON s.sustancia_id = vs.sustancia_id
        WHERE vs.vmp_id = :vmp
        GROUP BY s.sustancia_dsc
        ORDER BY min(length(vs.sust_orden)), min(vs.sust_orden), s.sustancia_dsc");
    $st->execute(array('vmp' => (string)$vmpId));
    return $st->fetchAll(PDO::FETCH_COLUMN);
}

// Otros productos con exactamente la misma composición (mismo conjunto de sustancias).
function vd_relacionados(PDO $pdo, $vmpId, $ampId, $limite = 20) {
    $st = $pdo->prepare("
        WITH comp AS (
            SELECT vmp_id, array_agg(DISTINCT sustancia_id ORDER BY sustancia_id) AS s
            FROM dnma.vmp_sustancia GROUP BY vmp_id
        )
        SELECT a.amp_id, a.amp_dsc, v.vmp_dsc, l.nombre AS laboratorio
        FROM dnma.amp a
        JOIN comp c ON c.vmp_id = a.vmp_id
        JOIN dnma.vmp v ON v.vmp_id = a.vmp_id
        LEFT JOIN dnma.laboratorio l ON l.lab_id = a.laboratorio_id
        WHERE c.s = (SELECT s FROM comp WHERE vmp_id = :vmp) AND a.amp_id <> :amp
        ORDER BY (a.comercializado = 'Comercializado') DESC, a.amp_dsc
        LIMIT " . (int)$limite);
    $st->execute(array('vmp' => (string)$vmpId, 'amp' => (string)$ampId));
    return $st->fetchAll();
}

// Búsqueda por nombre comercial o genérico (que incluye el principio activo). Mínimo 2 caracteres.
function vd_buscar(PDO $pdo, $q, $limite = 30) {
    $q = fh_normalizar($q);
    if (mb_strlen($q, 'UTF-8') < 2) {
        return array();
    }
    $like = '%' . strtr($q, array('\\' => '\\\\', '%' => '\%', '_' => '\_')) . '%';
    $inicio = strtr($q, array('\\' => '\\\\', '%' => '\%', '_' => '\_')) . '%';
    $amp = vd_norm_sql('a.amp_dsc');
    $vmp = vd_norm_sql('v.vmp_dsc');
    $st = $pdo->prepare("
        SELECT a.amp_id, a.amp_dsc, v.vmp_dsc, l.nombre AS laboratorio, a.comercializado
        FROM dnma.amp a
        JOIN dnma.vmp v ON v.vmp_id = a.vmp_id
        LEFT JOIN dnma.laboratorio l ON l.lab_id = a.laboratorio_id
        WHERE ($amp LIKE :like OR $vmp LIKE :like)
          AND EXISTS (SELECT 1 FROM dnma.vmp_sustancia vs WHERE vs.vmp_id = a.vmp_id)
        ORDER BY ($amp LIKE :inicio) DESC, (a.comercializado = 'Comercializado') DESC, a.amp_dsc
        LIMIT " . (int)$limite);
    $st->execute(array('like' => $like, 'inicio' => $inicio));
    return $st->fetchAll();
}

// Todas las páginas de producto (las que tienen composición), para el sitemap.
function vd_sitemap_filas(PDO $pdo) {
    return $pdo->query("
        SELECT a.amp_id, a.amp_dsc
        FROM dnma.amp a
        WHERE EXISTS (SELECT 1 FROM dnma.vmp_sustancia vs WHERE vs.vmp_id = a.vmp_id)
        ORDER BY a.amp_id")->fetchAll();
}
