<?php
// HTML del resultado del verificador: lo usan la página y el email (en el email con estilos inline).
require_once __DIR__ . '/verificador.php';

const VR_ROTULO_GRAVEDAD = array('importante' => 'Importante', 'moderada' => 'Moderada', 'leve' => 'Leve', 'incierta' => 'A revisar');

function vr_titulo_resumen(array $r) {
    if ($r['total'] === 0) return 'No encontramos interacciones ni advertencias en nuestras fuentes';
    $t = $r['total'] === 1 ? 'Encontramos 1 posible problema' : 'Encontramos ' . $r['total'] . ' posibles problemas';
    if ($r['importantes']) {
        $t .= $r['importantes'] === 1 ? ' (1 importante)' : ' (' . $r['importantes'] . ' importantes)';
    }
    return $t;
}

// $email = true → estilos inline y sin <details> (los clientes de correo no lo soportan bien).
function vr_html_alertas(array $alertas, $email = false) {
    $colores = array('importante' => '#B42318', 'moderada' => '#B54708', 'leve' => '#0B7A88', 'incierta' => '#5B636E');
    ob_start();
    foreach ($alertas as $a) {
        $c = $colores[$a['gravedad']];
        $prods = $a['productos'] ? implode(' + ', $a['productos']) : '';
        if ($email): ?>
<div style="border:1px solid #DDE2E7;border-left:5px solid <?php echo $c; ?>;padding:14px 16px;margin:0 0 14px;font-family:Arial,sans-serif;">
  <p style="margin:0 0 4px;font-size:12px;font-weight:bold;color:<?php echo $c; ?>;text-transform:uppercase;"><?php echo vd_e(VR_ROTULO_GRAVEDAD[$a['gravedad']]); ?></p>
  <p style="margin:0 0 6px;font-size:16px;font-weight:bold;color:#212832;"><?php echo vd_e($a['mensaje']['titulo']); ?><?php echo $prods ? ': ' . vd_e($prods) : ''; ?></p>
  <p style="margin:0 0 10px;font-size:15px;line-height:1.5;color:#2F343B;"><?php echo vd_e($a['mensaje']['texto']); ?></p>
  <p style="margin:0 0 4px;font-size:13px;font-weight:bold;color:#5B636E;">Detalle para tu médico:</p>
  <?php foreach ($a['detalle'] as $d): ?>
  <p style="margin:0 0 4px;font-size:13px;line-height:1.45;color:#5B636E;"><?php echo vd_e($d['texto']); ?> <em>(Fuente: <?php echo vd_e($d['fuente']); ?>)</em></p>
  <?php endforeach; ?>
</div>
        <?php else: ?>
<article class="vr-alerta vr-<?php echo vd_e($a['gravedad']); ?>">
    <p class="vr-gravedad"><?php echo vd_e(VR_ROTULO_GRAVEDAD[$a['gravedad']]); ?></p>
    <h3 class="vd-h3"><?php echo vd_e($a['mensaje']['titulo']); ?></h3>
    <?php if ($prods): ?><p class="vr-productos"><?php echo vd_e($prods); ?></p><?php endif; ?>
    <p class="vr-mensaje"><?php echo vd_e($a['mensaje']['texto']); ?></p>
    <details class="vr-detalle">
        <summary>Detalle para tu médico</summary>
        <ul>
            <?php foreach ($a['detalle'] as $d): ?>
            <li><?php echo vd_e($d['texto']); ?> <span class="vr-fuente">Fuente: <?php echo vd_e($d['fuente']); ?></span></li>
            <?php endforeach; ?>
        </ul>
    </details>
</article>
        <?php endif;
    }
    return ob_get_clean();
}

// Atribución de las fuentes usadas (DDInter exige atribución y licencia).
function vr_fuentes_usadas(array $alertas) {
    $f = array();
    foreach ($alertas as $a) {
        foreach ($a['detalle'] as $d) $f[$d['fuente']] = true;
    }
    $txt = 'Fuentes: catálogo DNMA (Uruguay); interacciones y advertencias de Consilio a partir de ';
    $partes = array('AEMPS (España)', 'MED-RT (NLM, EE.UU.)', 'openFDA (FDA, EE.UU.)');
    if (isset($f['DDInter'])) {
        $partes[] = 'DDInter 2.0 (ddinter2.scbdd.com, licencia CC BY-NC-SA 4.0)';
    }
    return $txt . implode(', ', $partes) . '.';
}
