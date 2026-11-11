<?php
// Página pública de un producto del DNMA con la ficha técnica de referencia de su principio activo
// (AEMPS, vía Consilio). URL: /medicamentos/<slug>-<amp_id> (ver .htaccess).
require_once __DIR__ . '/../inc/vademecum-db.php';
require_once __DIR__ . '/../inc/vademecum-consilio.php';
require_once __DIR__ . '/../inc/vademecum-layout.php';

function vd_404() {
    http_response_code(404);
    vd_head(array(
        'title' => 'Medicamento no encontrado | Recetalia',
        'description' => 'El medicamento buscado no está en el catálogo.',
        'canonical' => '/medicamentos/',
        'robots' => 'noindex',
    ));
    ?>
    <main id="contenido" class="vd-main"><div class="container vd-contenedor">
        <h1 class="vd-h1">No encontramos ese medicamento</h1>
        <p>Puede que el enlace esté mal escrito o que el producto ya no figure en el catálogo.</p>
        <p><a class="vd-boton" href="/medicamentos/">Buscar un medicamento</a></p>
    </div></main>
    <?php
    vd_pie();
    exit;
}

$ruta = vd_parse_ruta(isset($_GET['ruta']) ? $_GET['ruta'] : '');
if ($ruta === null) {
    vd_404();
}

try {
    $pdo = vd_pdo();
    $p = vd_producto($pdo, $ruta['id']);
    $sustancias = $p ? vd_sustancias($pdo, $p['vmp_id']) : array();
    $relacionados = $p ? vd_relacionados($pdo, $p['vmp_id'], $p['amp_id']) : array();
    $pdo = null;
} catch (PDOException $e) {
    error_log('vademecum producto: ' . $e->getMessage());
    http_response_code(503);
    header('Retry-After: 300');
    echo 'Servicio no disponible momentáneamente.';
    exit;
}

if (!$p || !$sustancias) {
    vd_404();
}

$canonica = vd_url($p['amp_id'], $p['amp_dsc']);
if ($ruta['slug'] !== vd_slug($p['amp_dsc'])) {
    header('Location: ' . $canonica, true, 301);
    exit;
}

$ficha = vd_ficha($sustancias);
$f = $ficha['data'];
$secciones = $ficha['estado'] === 'ok' ? vd_secciones($f['secciones']) : array();
$fuente = ($ficha['estado'] === 'ok' && isset($f['fuente'])) ? $f['fuente'] : null;

$nombre = vd_nombre($p['amp_dsc']);
$generico = $p['vmp_dsc'];
$condvta = vd_condvta($p['condvta_id']);
$comercializado = $p['comercializado'] === 'Comercializado';

header('Cache-Control: public, max-age=3600');

vd_head(array(
    'title' => $nombre . ' — ' . $generico . ' | Recetalia',
    'description' => vd_descripcion($nombre, $generico, $p['laboratorio']),
    'canonical' => $canonica,
    'og_type' => 'article',
    'jsonld' => array(
        '@context' => 'https://schema.org',
        '@type' => 'Drug',
        'name' => $nombre,
        'nonProprietaryName' => $generico,
        'activeIngredient' => implode(', ', $sustancias),
        'manufacturer' => $p['laboratorio'] ? array('@type' => 'Organization', 'name' => $p['laboratorio']) : null,
        'url' => vd_url_absoluta($canonica),
    ),
));
?>
<main id="contenido" class="vd-main">
    <div class="container vd-contenedor">
        <nav class="vd-migas" aria-label="Ruta de navegación">
            <a href="/">Inicio</a> <span aria-hidden="true">›</span>
            <a href="/medicamentos/">Medicamentos</a> <span aria-hidden="true">›</span>
            <span aria-current="page"><?php echo vd_e($nombre); ?></span>
        </nav>

        <header class="vd-producto">
            <p class="vd-sobretitulo">Medicamento registrado en Uruguay</p>
            <h1 class="vd-h1"><?php echo vd_e($nombre); ?></h1>
            <p class="vd-generico"><?php echo vd_e(vd_nombre($generico)); ?></p>
            <dl class="vd-datos">
                <div><dt>Principio activo</dt><dd><?php echo vd_e(implode(' + ', $sustancias)); ?></dd></div>
                <?php if ($p['laboratorio']): ?><div><dt>Laboratorio</dt><dd><?php echo vd_e($p['laboratorio']); ?></dd></div><?php endif; ?>
                <?php if ($p['nro_msp']): ?><div><dt>Registro MSP</dt><dd><?php echo vd_e($p['nro_msp']); ?></dd></div><?php endif; ?>
                <div><dt>Estado</dt><dd><?php echo $comercializado ? 'Comercializado' : 'No comercializado'; ?></dd></div>
                <?php if ($condvta): ?><div><dt>Condición de venta</dt><dd><?php echo vd_e($condvta); ?></dd></div><?php endif; ?>
                <?php if ($ficha['estado'] === 'ok' && !empty($f['atc']['codigo'])): ?>
                <div><dt>Código ATC</dt><dd><?php echo vd_e($f['atc']['codigo']); ?><?php if (!empty($f['atc']['nombre'])): ?> · <?php echo vd_e($f['atc']['nombre']); ?><?php endif; ?></dd></div>
                <?php endif; ?>
            </dl>
        </header>

        <section class="vd-ficha" aria-labelledby="vd-ficha-titulo">
            <h2 id="vd-ficha-titulo" class="vd-h2">Ficha técnica de referencia del principio activo</h2>
            <?php if ($ficha['estado'] === 'ok' && $secciones): ?>
                <p class="vd-ficha-origen">Texto oficial de la AEMPS (España), sin modificaciones. Puede diferir del producto registrado en Uruguay.</p>
                <?php foreach ($secciones as $s): ?>
                <section class="vd-seccion" id="seccion-<?php echo vd_e(str_replace('.', '-', $s['seccion'])); ?>">
                    <h3 class="vd-h3"><?php echo vd_e($s['rotulo']); ?></h3>
                    <div class="vd-texto" data-plegable><?php echo $s['html']; ?></div>
                </section>
                <?php endforeach; ?>
            <?php elseif ($ficha['estado'] === 'no_disponible'): ?>
                <p class="vd-estado">La ficha técnica no está disponible en este momento. Volvé a intentarlo en unos minutos.</p>
            <?php else: ?>
                <p class="vd-estado">No hay ficha técnica de referencia para este principio activo.</p>
            <?php endif; ?>
        </section>

        <aside class="vd-fuente" aria-label="Fuente y aviso">
            <?php if ($fuente): ?>
            <p><strong>Fuente:</strong> ficha técnica de la Agencia Española de Medicamentos y Productos Sanitarios (AEMPS) de
                <em><?php echo vd_e($fuente['nombre_ref']); ?></em> (nº de registro <?php echo vd_e($fuente['nregistro']); ?>)<?php
                $fecha = vd_fecha(isset($fuente['fecha_ficha']) ? $fuente['fecha_ficha'] : null);
                echo $fecha ? ', actualizada el ' . vd_e($fecha) : ''; ?>.
                Es la referencia española del principio activo y puede diferir del producto registrado en Uruguay.
                <?php if (!empty($fuente['url_ficha'])): ?>
                <a href="<?php echo vd_e($fuente['url_ficha']); ?>" target="_blank" rel="noopener nofollow">Ver ficha completa en la AEMPS<span class="vd-sr"> (abre en otra pestaña)</span> ↗</a>
                <?php endif; ?>
            </p>
            <?php endif; ?>
            <p class="vd-aviso"><?php echo vd_e(VD_AVISO); ?></p>
        </aside>

        <?php if ($relacionados): ?>
        <section class="vd-relacionados" aria-labelledby="vd-rel-titulo">
            <h2 id="vd-rel-titulo" class="vd-h2">Otros productos con <?php echo vd_e(implode(' + ', $sustancias)); ?></h2>
            <ul class="vd-lista">
                <?php foreach ($relacionados as $r): ?>
                <li><a class="vd-item" href="<?php echo vd_e(vd_url($r['amp_id'], $r['amp_dsc'])); ?>">
                    <span class="vd-item-nombre"><?php echo vd_e(vd_nombre($r['amp_dsc'])); ?></span>
                    <span class="vd-item-detalle"><?php echo vd_e($r['vmp_dsc']); ?><?php echo $r['laboratorio'] ? ' · ' . vd_e($r['laboratorio']) : ''; ?></span>
                </a></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
    </div>
</main>
<?php vd_pie(); ?>
