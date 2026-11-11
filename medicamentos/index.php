<?php
// Buscador público de medicamentos del DNMA. Con JS busca mientras se escribe (buscar.php);
// sin JS, el formulario manda ?q= y la lista sale renderizada desde el servidor.
require_once __DIR__ . '/../inc/vademecum-db.php';
require_once __DIR__ . '/../inc/vademecum-layout.php';

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$resultados = array();
$error = false;
if ($q !== '') {
    try {
        $resultados = vd_buscar(vd_pdo(), $q);
    } catch (PDOException $e) {
        error_log('vademecum buscar: ' . $e->getMessage());
        $error = true;
    }
}

header('Cache-Control: public, max-age=600');
vd_head(array(
    'title' => 'Medicamentos: ficha técnica, indicaciones y posología | Recetalia',
    'description' => 'Buscá cualquier medicamento registrado en Uruguay y consultá su ficha técnica de referencia: indicaciones, posología, contraindicaciones, interacciones y reacciones adversas.',
    'canonical' => '/medicamentos/',
    'robots' => $q !== '' ? 'noindex, follow' : null,
));
?>
<main id="contenido" class="vd-main">
    <section class="vd-hero">
        <div class="container vd-contenedor">
            <h1 class="vd-h1">Medicamentos</h1>
            <div class="vd-columnas">
            <section class="vd-col" aria-labelledby="vd-col-medicos">
            <p class="vd-col-etiqueta"><?php echo rc_icono('stethoscope'); ?>Médicos</p>
            <h2 id="vd-col-medicos" class="vd-h2">Información técnica de medicamentos</h2>
            <p class="vd-bajada">Buscá cualquier medicamento registrado en Uruguay y consultá su ficha técnica de referencia: indicaciones, posología, contraindicaciones e interacciones.</p>
            <form class="vd-buscador" action="/medicamentos/" method="get" role="search">
                <label for="vd-q" class="vd-etiqueta">Nombre o principio activo</label>
                <div class="vd-buscador-fila">
                    <input id="vd-q" name="q" type="search" value="<?php echo vd_e($q); ?>" placeholder="Ej.: perifar o paracetamol"
                           autocomplete="off" minlength="2" maxlength="80" aria-describedby="vd-ayuda" aria-controls="vd-resultados">
                    <button type="submit" class="vd-boton">Buscar</button>
                </div>
                <p id="vd-ayuda" class="vd-ayuda">Escribí al menos 2 letras. No hace falta poner tildes.</p>
            </form>
            </section>
            <section class="vd-col vd-col-pacientes" aria-labelledby="vd-col-pacientes">
            <p class="vd-col-etiqueta"><?php echo rc_icono('heart-pulse'); ?>Pacientes</p>
            <h2 id="vd-col-pacientes" class="vd-h2">Mis medicamentos</h2>
            <p class="vd-bajada">¿Tomás más de un medicamento? Revisá si se llevan bien entre sí y con tu situación (edad, embarazo, alergias, enfermedades), explicado en lenguaje simple.</p>
            <p><a class="vd-boton" href="/medicamentos/interacciones/"><?php echo rc_icono('list-check'); ?>Revisar mis medicamentos</a></p>
            </section>
            </div>
        </div>
    </section>

    <div class="container vd-contenedor">
        <p id="vd-contador" class="vd-contador" aria-live="polite"><?php
            if ($q !== '' && !$error) {
                echo count($resultados) ? count($resultados) . (count($resultados) === 1 ? ' resultado' : ' resultados') : '';
            }
        ?></p>
        <ul id="vd-resultados" class="vd-lista">
            <?php foreach ($resultados as $r): ?>
            <li><a class="vd-item" href="<?php echo vd_e(vd_url($r['amp_id'], $r['amp_dsc'])); ?>">
                <span class="vd-item-nombre"><?php echo vd_e(vd_nombre($r['amp_dsc'])); ?></span>
                <span class="vd-item-detalle"><?php echo vd_e($r['vmp_dsc']); ?><?php echo $r['laboratorio'] ? ' · ' . vd_e($r['laboratorio']) : ''; ?></span>
            </a></li>
            <?php endforeach; ?>
        </ul>
        <?php if ($error): ?>
            <p class="vd-estado">No pudimos buscar en este momento. Volvé a intentarlo en unos minutos.</p>
        <?php elseif ($q !== '' && !$resultados): ?>
            <p class="vd-estado" id="vd-vacio">No encontramos medicamentos con «<?php echo vd_e($q); ?>». Probá con el principio activo o con menos letras.</p>
        <?php endif; ?>

        <aside class="vd-fuente vd-fuente-inicio" aria-label="Sobre esta información">
            <h2 class="vd-h3">De dónde sale esta información</h2>
            <p>El catálogo de productos es el Diccionario Nacional de Medicamentos y Afines (DNMA) de Uruguay. La ficha técnica de
                cada principio activo es el texto oficial publicado por la Agencia Española de Medicamentos y Productos Sanitarios
                (AEMPS), sin modificaciones, con enlace al original. Uruguay no publica fichas técnicas nacionales.</p>
            <p class="vd-aviso"><?php echo vd_e(VD_AVISO); ?></p>
        </aside>
    </div>
</main>
<?php vd_pie(); ?>
