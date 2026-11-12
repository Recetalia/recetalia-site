<?php
// Sitemap de las páginas de producto (≈ 6.900; el límite por archivo es 50.000).
require_once __DIR__ . '/../inc/vademecum-db.php';
require_once __DIR__ . '/../inc/vademecum-layout.php'; // vd_url_absoluta

try {
    $filas = vd_sitemap_filas(vd_pdo());
} catch (PDOException $e) {
    error_log('vademecum sitemap: ' . $e->getMessage());
    http_response_code(503);
    exit;
}

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=86400');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
echo '<url><loc>', vd_e(vd_url_absoluta('/medicamentos/')), '</loc></url>', "\n";
foreach ($filas as $r) {
    echo '<url><loc>', vd_e(vd_url_absoluta(vd_url($r['amp_id'], $r['amp_dsc']))), '</loc></url>', "\n";
}
echo '</urlset>', "\n";
