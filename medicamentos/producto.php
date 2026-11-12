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
    <main id="contenido" class="vd-main"><div class="vd-contenedor vd-404">
        <div class="vd-estado-tarjeta">
            <span class="vd-estado-icono"><?php echo rc_icono('search'); ?></span>
            <h1 class="vd-h1">No encontramos ese medicamento</h1>
            <p>Puede que el enlace esté mal escrito o que el producto ya no figure en el catálogo.</p>
            <form class="vd-404-buscar" action="/medicamentos/" method="get" role="search">
                <label for="vd-404-q" class="vd-etiqueta">Nombre o principio activo</label>
                <div class="vd-buscador-fila">
                    <input id="vd-404-q" name="q" type="search" placeholder="Ej.: perifar o paracetamol" minlength="2" maxlength="80" autocomplete="off">
                    <button type="submit" class="vd-boton"><?php echo rc_icono('search'); ?><span>Buscar</span></button>
                </div>
            </form>
            <p class="vd-estado-acciones"><a class="vd-enlace-icono" href="/medicamentos/"><?php echo rc_icono('arrow-left'); ?>Volver a Medicamentos</a></p>
        </div>
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
$vdIconos = array('4.1' => 'clipboard-check', '4.2' => 'clock', '4.3' => 'ban', '4.4' => 'alert', '4.5' => 'cruce',
    '4.6' => 'baby', '4.8' => 'activity');
?>
<main id="contenido" class="vd-main vd-producto-pagina">
    <div class="vd-contenedor vd-contenedor-ancho">
        <nav class="vd-migas" aria-label="Ruta de navegación">
            <a href="/">Inicio</a> <span aria-hidden="true">›</span>
            <a href="/medicamentos/">Medicamentos</a> <span aria-hidden="true">›</span>
            <span aria-current="page"><?php echo vd_e($nombre); ?></span>
        </nav>

        <div class="vd-ficha-grilla">
            <header class="vd-producto">
                <p class="vd-sobretitulo"><?php echo rc_icono('pill', 'rc-icono rc-icono-chico'); ?>Medicamento registrado en Uruguay</p>
                <h1 class="vd-h1"><?php echo vd_e($nombre); ?></h1>
                <p class="vd-generico"><?php echo vd_e(vd_nombre($generico)); ?></p>
                <dl class="vd-datos">
                    <div><dt>Principio activo</dt><dd><?php echo vd_e(implode(' + ', $sustancias)); ?></dd></div>
                    <?php if ($p['laboratorio']): ?><div><dt>Laboratorio</dt><dd><?php echo vd_e($p['laboratorio']); ?></dd></div><?php endif; ?>
                    <?php if ($p['nro_msp']): ?><div><dt>Registro MSP</dt><dd><?php echo vd_e($p['nro_msp']); ?></dd></div><?php endif; ?>
                    <div class="<?php echo $comercializado ? 'vd-dato-ok' : 'vd-dato-no'; ?>"><dt>Estado</dt><dd><?php echo $comercializado ? 'Comercializado' : 'No comercializado'; ?></dd></div>
                    <?php if ($condvta): ?><div><dt>Condición de venta</dt><dd><?php echo vd_e($condvta); ?></dd></div><?php endif; ?>
                    <?php if ($ficha['estado'] === 'ok' && !empty($f['atc']['codigo'])): ?>
                    <div><dt>Código ATC</dt><dd><?php echo vd_e($f['atc']['codigo']); ?><?php if (!empty($f['atc']['nombre'])): ?> · <?php echo vd_e($f['atc']['nombre']); ?><?php endif; ?></dd></div>
                    <?php endif; ?>
                </dl>
            </header>

            <div class="vd-lateral">
                <section class="vd-lateral-tarjeta vd-lateral-lista" aria-labelledby="vd-lista-titulo">
                    <h2 id="vd-lista-titulo" class="vd-lateral-titulo"><?php echo rc_icono('list-check'); ?>Interacciones</h2>
                    <p>¿Tomás este medicamento junto con otros? Agregalo a tu lista y revisá si se llevan bien.</p>
                    <p class="vr-agregar-fila">
                        <button type="button" class="vd-boton vr-agregar" data-vr-agregar data-id="<?php echo vd_e($p['amp_id']); ?>"
                                data-nombre="<?php echo vd_e($nombre); ?>" data-detalle="<?php echo vd_e($generico); ?>">Agregar a mi lista para revisar interacciones</button>
                        <a class="vr-ir" href="/medicamentos/interacciones/">Revisar interacciones<?php echo rc_icono('arrow-right', 'rc-icono rc-icono-chico'); ?></a>
                    </p>
                </section>
                <?php if ($ficha['estado'] === 'ok' && $secciones): ?>
                <nav class="vd-lateral-tarjeta vd-indice" aria-labelledby="vd-indice-titulo">
                    <h2 id="vd-indice-titulo" class="vd-lateral-titulo"><?php echo rc_icono('list'); ?>En esta ficha</h2>
                    <ul>
                        <?php foreach ($secciones as $s): ?>
                        <li><a href="#seccion-<?php echo vd_e(str_replace('.', '-', $s['seccion'])); ?>"><?php echo rc_icono(isset($vdIconos[$s['seccion']]) ? $vdIconos[$s['seccion']] : 'file-text', 'rc-icono rc-icono-chico'); ?><?php echo vd_e($s['rotulo']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <aside class="vd-lateral-tarjeta vd-fuente" aria-label="Fuente y aviso">
                    <h2 class="vd-lateral-titulo"><?php echo rc_icono('book-open'); ?>Fuente</h2>
                    <?php if ($fuente): ?>
                    <p>Ficha técnica de la Agencia Española de Medicamentos y Productos Sanitarios (AEMPS) de
                        <em><?php echo vd_e($fuente['nombre_ref']); ?></em> (nº de registro <?php echo vd_e($fuente['nregistro']); ?>)<?php
                        $fecha = vd_fecha(isset($fuente['fecha_ficha']) ? $fuente['fecha_ficha'] : null);
                        echo $fecha ? ', actualizada el ' . vd_e($fecha) : ''; ?>.
                        Es la referencia española del principio activo y puede diferir del producto registrado en Uruguay.</p>
                    <?php if (!empty($fuente['url_ficha'])): ?>
                    <p><a class="vd-enlace-icono" href="<?php echo vd_e($fuente['url_ficha']); ?>" target="_blank" rel="noopener nofollow">Ver ficha completa en la AEMPS<span class="vd-sr"> (abre en otra pestaña)</span><?php echo rc_icono('external', 'rc-icono rc-icono-chico'); ?></a></p>
                    <?php endif; ?>
                    <?php endif; ?>
                    <p class="vd-aviso"><?php echo vd_e(VD_AVISO); ?></p>
                </aside>
            </div>

            <section class="vd-ficha" aria-labelledby="vd-ficha-titulo">
                <h2 id="vd-ficha-titulo" class="vd-h2">Ficha técnica de referencia del principio activo</h2>
                <?php if ($ficha['estado'] === 'ok' && $secciones): ?>
                    <p class="vd-ficha-origen"><?php echo rc_icono('info'); ?><span>Texto oficial de la AEMPS (España), sin modificaciones, de la ficha de
                        <strong><?php echo vd_e($fuente ? $fuente['nombre_ref'] : ''); ?></strong>, el medicamento español de referencia con el
                        mismo principio activo: por eso el texto puede nombrar esa marca. Puede diferir del producto registrado en Uruguay.</span></p>
                    <?php foreach ($secciones as $s): ?>
                    <section class="vd-seccion" id="seccion-<?php echo vd_e(str_replace('.', '-', $s['seccion'])); ?>">
                        <h3 class="vd-h3 vd-seccion-titulo"><span class="vd-seccion-icono"><?php echo rc_icono(isset($vdIconos[$s['seccion']]) ? $vdIconos[$s['seccion']] : 'file-text'); ?></span><?php echo vd_e($s['rotulo']); ?></h3>
                        <div class="vd-texto" data-plegable><?php echo $s['html']; ?></div>
                    </section>
                    <?php endforeach; ?>
                <?php elseif ($ficha['estado'] === 'no_disponible'): ?>
                    <p class="vd-estado">La ficha técnica no está disponible en este momento. Volvé a intentarlo en unos minutos.</p>
                <?php else: ?>
                    <p class="vd-estado">No hay ficha técnica de referencia para este principio activo.</p>
                <?php endif; ?>
            </section>
        </div>

        <?php if ($relacionados): ?>
        <section class="vd-relacionados" aria-labelledby="vd-rel-titulo">
            <h2 id="vd-rel-titulo" class="vd-h2">Otros productos con <?php echo vd_e(implode(' + ', $sustancias)); ?></h2>
            <ul class="vd-lista vd-grilla-tarjetas">
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
<?php vd_pie('<script src="/js/verificador.js?v=4" defer></script>'); ?>
