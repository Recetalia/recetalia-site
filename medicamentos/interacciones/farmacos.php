<?php
// Autocompletado de alergias → Consilio /drugs/search. JSON [{nombre (canónico, lo que se manda), mostrar}].
// Se manda el nombre canónico (como la demo de Consilio): así la alergia coincide exacto con el fármaco.
require_once __DIR__ . '/../../inc/verificador-consilio.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');
header('X-Robots-Tag: noindex');

$q = trim(mb_substr(isset($_GET['q']) ? (string)$_GET['q'] : '', 0, 60, 'UTF-8'));
if (mb_strlen($q, 'UTF-8') < 3) {
    echo '[]';
    exit;
}
$r = vr_consilio('GET', '/drugs/search?' . http_build_query(array('q' => $q, 'limit' => 8)), null, 4);
$out = array();
foreach (isset($r['results']) ? $r['results'] : array() as $f) {
    if (!empty($f['name'])) {
        $out[] = array('nombre' => $f['name'], 'mostrar' => !empty($f['name_es']) ? $f['name_es'] : $f['name']);
    }
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
