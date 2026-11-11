<?php
// JSON para el buscador: [{nombre, detalle, url}], máx. 30. Mínimo 2 caracteres.
require_once __DIR__ . '/../inc/vademecum-db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=600');
header('X-Robots-Tag: noindex');

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
try {
    $filas = vd_buscar(vd_pdo(), mb_substr($q, 0, 80, 'UTF-8'));
} catch (PDOException $e) {
    error_log('vademecum buscar.json: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(array('error' => 'no_disponible'));
    exit;
}

echo json_encode(array_map(function ($r) {
    return array(
        'nombre' => vd_nombre($r['amp_dsc']),
        'detalle' => $r['vmp_dsc'] . ($r['laboratorio'] ? ' · ' . $r['laboratorio'] : ''),
        'url' => vd_url($r['amp_id'], $r['amp_dsc']),
    );
}, $filas), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
