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
<main id="contenido" class="vd-main vd-landing">
    <section class="vd-buscador-hero" aria-labelledby="vd-titulo">
        <div class="vd-contenedor vd-contenedor-ancho vd-hero-grilla">
            <div class="vd-hero-principal">
            <p class="vd-sobretitulo"><?php echo rc_icono('shield-check', 'rc-icono rc-icono-chico'); ?>Medicamentos registrados en Uruguay</p>
            <h1 id="vd-titulo" class="vd-h1">Medicamentos</h1>
            <p class="vd-bajada">Buscá cualquier medicamento registrado en Uruguay y consultá su ficha técnica de referencia: indicaciones, posología, contraindicaciones e interacciones.</p>
            <form class="vd-buscador" action="/medicamentos/" method="get" role="search">
                <label for="vd-q" class="vd-etiqueta">Nombre o principio activo</label>
                <div class="vd-buscador-caja">
                    <div class="vd-buscador-fila">
                        <input id="vd-q" name="q" type="search" value="<?php echo vd_e($q); ?>" placeholder="Ej.: perifar o paracetamol"
                               autocomplete="off" minlength="2" maxlength="80" aria-describedby="vd-ayuda"
                               role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="vd-sugerencias">
                        <button type="submit" class="vd-boton"><?php echo rc_icono('search'); ?><span>Buscar</span></button>
                    </div>
                    <div id="vd-panel" class="vd-panel" hidden>
                        <ul id="vd-sugerencias" class="vd-sugerencias" role="listbox" aria-label="Sugerencias"></ul>
                        <a id="vd-ver-todos" class="vd-panel-pie" href="/medicamentos/">Ver todos los resultados</a>
                    </div>
                </div>
                <p id="vd-sug-estado" class="rc-sr" aria-live="polite"></p>
                <p id="vd-ayuda" class="vd-ayuda">Escribí al menos 2 letras. No hace falta poner tildes.</p>
                <p class="vd-ejemplos"><span>Ejemplos:</span>
                    <a href="/medicamentos/?q=paracetamol">paracetamol</a>
                    <a href="/medicamentos/?q=ibuprofeno">ibuprofeno</a>
                    <a href="/medicamentos/?q=amoxicilina">amoxicilina</a>
                    <a href="/medicamentos/?q=losartan">losartán</a>
                </p>
            </form>
            </div>
            <aside class="vd-hero-pacientes" aria-labelledby="vd-hero-pac-titulo">
                <span class="vd-hero-pac-icono"><?php echo rc_icono('heart-pulse'); ?></span>
                <p class="vd-hero-pac-etiqueta">Pacientes</p>
                <h2 id="vd-hero-pac-titulo" class="vd-hero-pac-titulo">¿Tomás más de un medicamento?</h2>
                <p class="vd-hero-pac-texto">Revisá si se llevan bien entre sí y con tu situación (edad, embarazo, alergias, enfermedades), explicado en lenguaje simple.</p>
                <a class="vd-boton vd-hero-pac-boton" href="/medicamentos/interacciones/"><?php echo rc_icono('list-check'); ?>Revisar mis medicamentos</a>
            </aside>
        </div>
    </section>

    <?php if ($q !== ''): ?>
    <section class="vd-bloque vd-resultados-bloque" aria-labelledby="vd-res-titulo">
        <div class="vd-contenedor vd-contenedor-ancho">
            <h2 id="vd-res-titulo" class="vd-h2">Resultados para «<?php echo vd_e($q); ?>»</h2>
            <p id="vd-contador" class="vd-contador" aria-live="polite"><?php
                if (!$error) {
                    echo count($resultados) ? count($resultados) . (count($resultados) === 1 ? ' resultado' : ' resultados') : '';
                }
            ?></p>
            <ul id="vd-resultados" class="vd-lista vd-grilla-tarjetas">
                <?php foreach ($resultados as $r): ?>
                <li><a class="vd-item" href="<?php echo vd_e(vd_url($r['amp_id'], $r['amp_dsc'])); ?>">
                    <span class="vd-item-nombre"><?php echo vd_e(vd_nombre($r['amp_dsc'])); ?></span>
                    <span class="vd-item-detalle"><?php echo vd_e($r['vmp_dsc']); ?><?php echo $r['laboratorio'] ? ' · ' . vd_e($r['laboratorio']) : ''; ?></span>
                </a></li>
                <?php endforeach; ?>
            </ul>
            <?php if ($error): ?>
                <p class="vd-estado">No pudimos buscar en este momento. Volvé a intentarlo en unos minutos.</p>
            <?php elseif (!$resultados): ?>
                <p class="vd-estado" id="vd-vacio">No encontramos medicamentos con «<?php echo vd_e($q); ?>». Probá con el principio activo o con menos letras.</p>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="vd-bloque" aria-labelledby="vd-perfiles-titulo">
        <div class="vd-contenedor vd-contenedor-ancho">
            <h2 id="vd-perfiles-titulo" class="rc-sr">¿Qué necesitás?</h2>
            <div class="vd-perfiles">
                <section class="vd-perfil vd-perfil-medicos" aria-labelledby="vd-col-medicos">
                    <span class="vd-perfil-icono"><?php echo rc_icono('stethoscope'); ?></span>
                    <p class="vd-col-etiqueta">Médicos</p>
                    <h3 id="vd-col-medicos" class="vd-h2">Información técnica de medicamentos</h3>
                    <p class="vd-perfil-bajada">La ficha técnica oficial de referencia de cada principio activo, ordenada por secciones: indicaciones, posología, contraindicaciones, advertencias, interacciones, embarazo y reacciones adversas.</p>
                    <p class="vd-perfil-cta"><a class="vd-boton vd-boton-claro" href="#vd-q"><?php echo rc_icono('search'); ?>Buscar un medicamento</a></p>
                </section>
                <section class="vd-perfil vd-perfil-pacientes" aria-labelledby="vd-col-pacientes">
                    <span class="vd-perfil-icono"><?php echo rc_icono('heart-pulse'); ?></span>
                    <p class="vd-col-etiqueta">Pacientes</p>
                    <h3 id="vd-col-pacientes" class="vd-h2">Mis medicamentos</h3>
                    <p class="vd-perfil-bajada">¿Tomás más de un medicamento? Revisá si se llevan bien entre sí y con tu situación (edad, embarazo, alergias, enfermedades), explicado en lenguaje simple.</p>
                    <p class="vd-perfil-cta"><a class="vd-boton" href="/medicamentos/interacciones/"><?php echo rc_icono('list-check'); ?>Revisar mis medicamentos</a></p>
                    <svg class="vd-perfil-ilustra" viewBox="0 0 200 200" aria-hidden="true" focusable="false"><circle cx="100" cy="100" r="96" fill="none" stroke="currentColor" stroke-width="2" opacity=".35"/><circle cx="100" cy="100" r="66" fill="none" stroke="currentColor" stroke-width="2" opacity=".5"/><path d="M70 100h18l8-18 12 36 8-18h14" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </section>
            </div>
        </div>
    </section>

    <section class="vd-bloque vd-bloque-fondo" aria-labelledby="vd-como-titulo">
        <div class="vd-contenedor vd-contenedor-ancho">
            <h2 id="vd-como-titulo" class="vd-h2 vd-centrado">Cómo funciona</h2>
            <ol class="vd-pasos">
                <li><span class="vd-paso-icono"><?php echo rc_icono('search'); ?></span><h3 class="vd-h3">Buscá</h3><p>Escribí el nombre comercial o el principio activo.</p></li>
                <li><span class="vd-paso-icono"><?php echo rc_icono('file-text'); ?></span><h3 class="vd-h3">Consultá la ficha</h3><p>Cada producto muestra la ficha técnica de referencia de su principio activo, por secciones.</p></li>
                <li><span class="vd-paso-icono"><?php echo rc_icono('list-check'); ?></span><h3 class="vd-h3">Revisá tus medicamentos</h3><p>Armá tu lista y revisá interacciones y advertencias en lenguaje simple.</p></li>
            </ol>
        </div>
    </section>

    <section class="vd-bloque" aria-labelledby="vd-fuentes-titulo">
        <div class="vd-contenedor vd-contenedor-ancho vd-fuentes">
            <div class="vd-fuentes-texto">
                <p class="vd-col-etiqueta"><?php echo rc_icono('book-open', 'rc-icono rc-icono-chico'); ?>Fuentes oficiales</p>
                <h2 id="vd-fuentes-titulo" class="vd-h2">De dónde sale esta información</h2>
                <p>El catálogo de productos es el Diccionario Nacional de Medicamentos y Afines (DNMA) de Uruguay. La ficha técnica de
                    cada principio activo es el texto oficial publicado por la Agencia Española de Medicamentos y Productos Sanitarios
                    (AEMPS), sin modificaciones, con enlace al original. Uruguay no publica fichas técnicas nacionales.</p>
                <p class="vd-aviso vd-aviso-caja"><?php echo rc_icono('info'); ?><span><?php echo vd_e(VD_AVISO); ?></span></p>
            </div>
            <ul class="vd-fuentes-tarjetas">
                <li><span class="vd-paso-icono"><?php echo rc_icono('landmark'); ?></span><div><strong>DNMA · Uruguay</strong><span>Catálogo de productos registrados</span></div></li>
                <li><span class="vd-paso-icono"><?php echo rc_icono('globe'); ?></span><div><strong>AEMPS · España</strong><span>Ficha técnica oficial, sin modificaciones</span></div></li>
                <li><span class="vd-paso-icono"><?php echo rc_icono('external'); ?></span><div><strong>Enlace al original</strong><span>En cada ficha, para consultar la fuente</span></div></li>
            </ul>
        </div>
    </section>
</main>
<?php vd_pie(); ?>
