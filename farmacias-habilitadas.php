<?php
// Listado público de farmacias habilitadas. Renderizado en el servidor; el script
// de abajo sólo muestra u oculta filas. Spec: doc/plans/2026-09-04-farmacias-habilitadas-rediseno-design.md
require_once __DIR__ . '/inc/farmacias-habilitadas.php';

// Credenciales read-only (SELECT sobre pharmacy/localities/regions), por env del
// contenedor (docker-compose.yml → recetalia-site). Requiere SSL.
$fhError = false;
$fhDatos = array('total' => 0, 'regiones' => array());

// PostgreSQL (base `recetalia`, schema `receta`) por PDO; SSL obligatorio (CA propia de DigitalOcean:
// cifrado sí, verificación de la cadena no — sslmode=require, como el MYSQLI_CLIENT_SSL_DONT_VERIFY de antes).
try {
    $dsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s;sslmode=require',
        getenv('SITE_DB_HOST'),
        (int)(getenv('SITE_DB_PORT') ?: 25060),
        getenv('SITE_DB_NAME') ?: 'recetalia'
    );
    $pdo = new PDO($dsn, getenv('SITE_DB_USER'), getenv('SITE_DB_PASS'), array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ));
    $fhDatos = fh_agrupar($pdo->query(FH_SQL)->fetchAll(PDO::FETCH_ASSOC));
    $pdo = null;
} catch (PDOException $e) {
    error_log('farmacias-habilitadas: ' . $e->getMessage());
    $fhError = true;
}

$fhRegiones = $fhDatos['regiones'];
$fhTotal = $fhDatos['total'];

function fh_e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/inc/layout.php';
rc_head(array(
    'title' => 'Farmacias habilitadas | Recetalia',
    'description' => 'Farmacias habilitadas para dispensar recetas digitales de Recetalia en todo el Uruguay.',
    'canonical' => rtrim(getenv('SITE_PUBLIC_URL') ?: 'https://recetalia.com', '/') . '/farmacias-habilitadas.php',
));
?>
<main id="contenido">
    <section class="rc-pagina-cabeza" aria-labelledby="fh-titulo">
        <div class="rc-container rc-angosto">
            <nav class="rc-migas" aria-label="Ruta de navegación">
                <ol>
                    <li><a href="/">Inicio</a><?php echo rc_icono('chevron-right'); ?></li>
                    <li><span aria-current="page">Farmacias habilitadas</span></li>
                </ol>
            </nav>
            <h1 id="fh-titulo">Farmacias habilitadas</h1>
            <?php if (!$fhError): ?>
            <p class="rc-bajada"><?php echo fh_plural($fhTotal, 'farmacia habilitada', 'farmacias habilitadas'); ?> en todo el país</p>
            <?php endif; ?>
        </div>
    </section>
    <section class="rc-seccion fh-seccion">
        <div class="rc-container rc-angosto">
            <?php if ($fhError): ?>
            <p class="rc-alerta rc-alerta-error fh-error"><?php echo rc_icono('alert'); ?><span>No pudimos cargar el listado en este momento. Volvé a intentarlo en unos minutos.</span></p>
            <?php else: ?>
            <div class="rc-campo fh-buscar">
                <label class="rc-label" for="fh-buscar">Buscar farmacia</label>
                <div class="rc-buscador">
                    <?php echo rc_icono('search'); ?>
                    <input type="search" id="fh-buscar" class="rc-input" autocomplete="off"
                           placeholder="Nombre, localidad o dirección">
                </div>
            </div>
            <p id="fh-contador" class="fh-contador" aria-live="polite" hidden></p>
            <div id="fh-acordeon">
                <?php $i = 0; foreach ($fhRegiones as $r): $i++; ?>
                <section class="fh-region rc-acordeon-item" data-region="<?php echo fh_e($r['nombre']); ?>">
                    <button type="button" class="fh-dep rc-acordeon-cabeza" aria-expanded="false" aria-controls="fh-lista-<?php echo $i; ?>">
                        <span><?php echo fh_e($r['nombre']); ?></span>
                        <span class="fh-dep-der"><span class="fh-cantidad"><?php echo $r['cantidad']; ?><span class="rc-sr"> farmacias</span></span><?php echo rc_icono('chevron-down'); ?></span>
                    </button>
                    <ul class="fh-lista" id="fh-lista-<?php echo $i; ?>">
                        <?php foreach ($r['farmacias'] as $f): ?>
                        <li class="fh-item" data-search="<?php echo fh_e($f['busqueda']); ?>">
                            <div>
                                <h3 class="fh-nombre"><?php echo fh_e($f['nombre']); ?></h3>
                                <p><?php echo fh_e(implode(' · ', array_filter(array($f['direccion'], $f['localidad']), 'strlen'))); ?></p>
                            </div>
                            <?php if ($f['telefono']): ?>
                            <a class="fh-tel" href="tel:<?php echo fh_e($f['telefono']['international']); ?>"><?php echo rc_icono('phone', 'rc-icono rc-icono-chico'); ?><?php echo fh_e($f['telefono']['national']); ?></a>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <?php endforeach; ?>
            </div>
            <p id="fh-vacio" class="rc-alerta fh-vacio" hidden>No encontramos farmacias con ese nombre.</p>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php
$fhScript = '';
if (!$fhError) {
    ob_start(); ?>
    <script>
    (function () {
        var input = document.getElementById('fh-buscar');
        var contador = document.getElementById('fh-contador');
        var vacio = document.getElementById('fh-vacio');
        var regiones = document.querySelectorAll('.fh-region');

        // Misma regla que fh_normalizar() en PHP: minúsculas, sin tildes, espacios colapsados.
        function normalizar(s) {
            return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, ' ').trim();
        }
        function plural(n, singular, pluralTxt) {
            return n + ' ' + (n === 1 ? singular : pluralTxt);
        }
        // Despliega o pliega la lista de un departamento animando max-height hasta su alto real.
        function abrir(region, abierta) {
            var lista = region.querySelector('.fh-lista');
            region.classList.toggle('abierta', abierta);
            region.querySelector('.fh-dep').setAttribute('aria-expanded', abierta ? 'true' : 'false');
            lista.style.maxHeight = abierta ? lista.scrollHeight + 'px' : '0px';
        }

        // Acordeón: abrir un departamento cierra el que estaba abierto; click en el abierto lo cierra.
        function alternar(region) {
            var estabaAbierta = region.classList.contains('abierta');
            for (var i = 0; i < regiones.length; i++) {
                if (regiones[i].classList.contains('abierta')) abrir(regiones[i], false);
            }
            if (!estabaAbierta) abrir(region, true);
        }

        // Con texto: busca en todo el país, abre los departamentos con coincidencias y
        // esconde los demás. Sin texto: todo visible y todo cerrado.
        function buscar() {
            var q = normalizar(input.value);
            var buscando = q !== '';
            var total = 0;
            for (var i = 0; i < regiones.length; i++) {
                var region = regiones[i];
                var items = region.querySelectorAll('.fh-item');
                var visibles = 0;
                for (var j = 0; j < items.length; j++) {
                    var ok = !buscando || items[j].getAttribute('data-search').indexOf(q) !== -1;
                    items[j].hidden = !ok;
                    if (ok) visibles++;
                }
                region.hidden = buscando && visibles === 0;
                abrir(region, buscando && visibles > 0);
                if (buscando) total += visibles;
            }
            contador.hidden = !buscando;
            contador.textContent = buscando ? plural(total, 'farmacia encontrada', 'farmacias encontradas') : '';
            vacio.hidden = !buscando || total > 0;
        }

        input.addEventListener('input', buscar);
        for (var k = 0; k < regiones.length; k++) {
            regiones[k].querySelector('.fh-dep').addEventListener('click', function () {
                alternar(this.parentNode);
            });
        }
    })();
    </script>
<?php
    $fhScript = ob_get_clean();
}
rc_pie($fhScript, true);
