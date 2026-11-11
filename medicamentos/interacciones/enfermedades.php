<?php
// Autocompletado de enfermedades (CIE-10 en castellano) → Consilio /conditions/search. JSON [{codigo, nombre}].
require_once __DIR__ . '/../../inc/verificador-consilio.php';
require_once __DIR__ . '/../../inc/vademecum.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');
header('X-Robots-Tag: noindex');

$q = trim(mb_substr(isset($_GET['q']) ? (string)$_GET['q'] : '', 0, 60, 'UTF-8'));
if (mb_strlen($q, 'UTF-8') < 3) {
    echo '[]';
    exit;
}
$r = vr_consilio('GET', '/conditions/search?' . http_build_query(array('q' => $q, 'limit' => 8)), null, 4);
$out = array();
foreach (isset($r['results']) ? $r['results'] : array() as $c) {
    if (!empty($c['code']) && !empty($c['name'])) {
        $out[] = array('codigo' => $c['code'], 'nombre' => $c['name']);
    }
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
