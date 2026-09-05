<?php
// Listado público de farmacias habilitadas. Renderizado en el servidor; el script
// de abajo sólo muestra u oculta filas. Spec: doc/plans/2026-09-04-farmacias-habilitadas-rediseno-design.md
require_once __DIR__ . '/inc/farmacias-habilitadas.php';

// Credenciales read-only (SELECT sobre pharmacy/localities/regions), por env del
// contenedor (docker-compose.yml → recetalia-site). Requiere SSL.
$fhError = false;
$fhDatos = array('total' => 0, 'regiones' => array());

mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = mysqli_init();
$mysqli->ssl_set(NULL, NULL, NULL, NULL, NULL);
$conectado = @$mysqli->real_connect(
    getenv('SITE_DB_HOST'),
    getenv('SITE_DB_USER'),
    getenv('SITE_DB_PASS'),
    getenv('SITE_DB_NAME') ?: 'recetali_receta',
    (int)(getenv('SITE_DB_PORT') ?: 25060),
    NULL,
    MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
);
if (!$conectado) {
    error_log('farmacias-habilitadas: no se pudo conectar a la DB: ' . mysqli_connect_error());
    $fhError = true;
} else {
    $mysqli->set_charset('utf8mb4');
    $res = $mysqli->query(FH_SQL);
    if ($res === false) {
        error_log('farmacias-habilitadas: consulta fallida: ' . $mysqli->error);
        $fhError = true;
    } else {
        $fhDatos = fh_agrupar($res->fetch_all(MYSQLI_ASSOC));
    }
    $mysqli->close();
}

$fhRegiones = $fhDatos['regiones'];
$fhTotal = $fhDatos['total'];
$fhInicial = fh_region_inicial($fhRegiones);
$fhCantidadInicial = $fhInicial !== '' ? $fhRegiones[$fhInicial]['cantidad'] : 0;

function fh_e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE HTML>
<html lang="es">

<head>
    <base href="https://recetalia.com/">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Farmacias habilitadas para dispensar recetas digitales de Recetalia en todo el Uruguay." />
    <meta name="author" content="Recetalia" />
    <title>Recetalia - Farmacias habilitadas</title>
    <link rel="shortcut icon" href="images/favicon-recetalia.png" type="image/x-icon">
    <link rel="icon" href="images/favicon-recetalia.png" type="image/x-icon">
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link rel="stylesheet" href="css/slimmenu.min.css" />
    <link rel="stylesheet" href="css/animate.min.css" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="css/responsive.css" />
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,700" rel="stylesheet">

    <style>
        /* Estilos propios del listado. Sólo colores que ya usa styles.css. */
        .fh-buscar { max-width: 560px; margin: 0 auto 40px; position: relative; }
        .fh-buscar .form-control { height: 48px; padding-left: 44px; font-size: 15px; }
        .fh-buscar .fa { position: absolute; left: 16px; top: 16px; color: #a6a6a6; }

        .fh-departamentos { list-style: none; padding: 0; margin: 0; background: #fff;
            box-shadow: -1px 0px 30px 0px rgba(0, 0, 0, 0.05); }
        .fh-dep { display: flex; justify-content: space-between; align-items: center; width: 100%;
            padding: 11px 18px; border: 0; border-left: 3px solid transparent; background: none;
            text-align: left; font-family: inherit; font-size: 14px; color: #212832; cursor: pointer; }
        .fh-dep:hover { border-left-color: #dbdbdb; background: #F4F6F8; }
        .fh-dep.activo { border-left-color: #2EA1B1; color: #2EA1B1; font-weight: 500; background: #F4F6F8; }
        .fh-dep .fh-cantidad { font-size: 12px; color: #7d7d7d; }
        .fh-dep.activo .fh-cantidad { color: #2EA1B1; }
        .fh-dep, .fh-dep .fh-cantidad { transition: all 0.15s ease-in-out; }
        .fh-select { margin-bottom: 20px; height: 48px; }

        .fh-contador { font-size: 14px; color: #7d7d7d; margin: 0 0 12px; }
        .fh-lista { list-style: none; padding: 0; margin: 0; background: #fff;
            box-shadow: -1px 0px 30px 0px rgba(0, 0, 0, 0.05); }
        .fh-item { display: flex; justify-content: space-between; align-items: center; gap: 16px;
            padding: 14px 20px; border-bottom: 1px solid #e5e5e5; }
        .fh-item:last-child { border-bottom: 0; }
        .fh-item h6 { font-size: 15px; font-weight: 500; margin: 0 0 2px; color: #212832; }
        .fh-item p { font-size: 13px; line-height: 20px; margin: 0; color: #7d7d7d; }
        .fh-item .fh-tel { white-space: nowrap; font-size: 14px; color: #2EA1B1; }
        .fh-item .fh-tel .fa { margin-right: 6px; }
        .fh-etiqueta { display: none; margin-left: 8px; padding: 1px 10px; border-radius: 12px;
            background: #F4F6F8; color: #2EA1B1; font-size: 12px; }
        .fh-lista.fh-buscando .fh-etiqueta { display: inline-block; }
        .fh-vacio, .fh-error { background: #fff; padding: 30px 20px; text-align: center; color: #7d7d7d; }
        [hidden] { display: none !important; }

        @media (max-width: 767px) {
            .fh-item { flex-direction: column; align-items: flex-start; gap: 4px; padding: 12px 16px; }
            .fh-buscar { margin-bottom: 24px; }
        }
    </style>
</head>

<body>
    <?php include_once("header.php"); ?>

    <section class="services-section padding-60-0 bg-color3 section-spacing">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="section-title margin-bottom-60 text-center">
                        <h4>Farmacias habilitadas</h4>
                        <?php if (!$fhError): ?>
                        <p><?php echo fh_plural($fhTotal, 'farmacia habilitada', 'farmacias habilitadas'); ?> en todo el país</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($fhError): ?>
            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto">
                    <p class="fh-error">No pudimos cargar el listado en este momento. Volvé a intentarlo en unos minutos.</p>
                </div>
            </div>
            <?php else: ?>
            <div class="fh-buscar">
                <i class="fa fa-search" aria-hidden="true"></i>
                <input type="search" id="fh-buscar" class="form-control" autocomplete="off"
                       placeholder="Buscar por nombre, localidad o dirección" aria-label="Buscar farmacia">
            </div>

            <div class="row">
                <aside class="col-md-4 col-lg-3">
                    <select id="fh-select" class="form-control fh-select d-md-none" aria-label="Departamento">
                        <?php foreach ($fhRegiones as $r): ?>
                        <option value="<?php echo fh_e($r['nombre']); ?>"<?php echo $r['nombre'] === $fhInicial ? ' selected' : ''; ?>><?php echo fh_e($r['nombre']); ?> (<?php echo $r['cantidad']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <ul class="fh-departamentos d-none d-md-block">
                        <?php foreach ($fhRegiones as $r): ?>
                        <li><button type="button" class="fh-dep<?php echo $r['nombre'] === $fhInicial ? ' activo' : ''; ?>" data-region="<?php echo fh_e($r['nombre']); ?>"><?php echo fh_e($r['nombre']); ?> <span class="fh-cantidad"><?php echo $r['cantidad']; ?></span></button></li>
                        <?php endforeach; ?>
                    </ul>
                </aside>

                <div class="col-md-8 col-lg-9">
                    <p id="fh-contador" class="fh-contador" aria-live="polite"><?php echo fh_plural($fhCantidadInicial, 'farmacia', 'farmacias'); ?> en <?php echo fh_e($fhInicial); ?></p>
                    <ul id="fh-lista" class="fh-lista">
                        <?php foreach ($fhRegiones as $r): foreach ($r['farmacias'] as $f): ?>
                        <li class="fh-item" data-region="<?php echo fh_e($r['nombre']); ?>" data-search="<?php echo fh_e($f['busqueda']); ?>"<?php echo $r['nombre'] === $fhInicial ? '' : ' hidden'; ?>>
                            <div>
                                <h6><?php echo fh_e($f['nombre']); ?></h6>
                                <p><?php echo fh_e(implode(' · ', array_filter(array($f['direccion'], $f['localidad']), 'strlen'))); ?><span class="fh-etiqueta"><?php echo fh_e($r['nombre']); ?></span></p>
                            </div>
                            <?php if ($f['telefono']): ?>
                            <a class="fh-tel" href="tel:<?php echo fh_e($f['telefono']['international']); ?>"><i class="fa fa-phone" aria-hidden="true"></i><?php echo fh_e($f['telefono']['national']); ?></a>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; endforeach; ?>
                    </ul>
                    <p id="fh-vacio" class="fh-vacio" hidden>No encontramos farmacias con ese nombre.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include_once("footer.php"); ?>

    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.slimmenu.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/custom.js"></script>

    <?php if (!$fhError): ?>
    <script>
    (function () {
        var input = document.getElementById('fh-buscar');
        var select = document.getElementById('fh-select');
        var lista = document.getElementById('fh-lista');
        var contador = document.getElementById('fh-contador');
        var vacio = document.getElementById('fh-vacio');
        var deps = document.querySelectorAll('.fh-dep');
        var items = lista.querySelectorAll('.fh-item');
        var region = select.value;

        // Misma regla que fh_normalizar() en PHP: minúsculas, sin tildes, espacios colapsados.
        function normalizar(s) {
            return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, ' ').trim();
        }
        function plural(n, singular, pluralTxt) {
            return n + ' ' + (n === 1 ? singular : pluralTxt);
        }

        function aplicar() {
            var q = normalizar(input.value);
            var buscando = q !== '';
            var visibles = 0;
            lista.classList.toggle('fh-buscando', buscando);
            for (var i = 0; i < items.length; i++) {
                var it = items[i];
                var ok = buscando
                    ? it.getAttribute('data-search').indexOf(q) !== -1
                    : it.getAttribute('data-region') === region;
                it.hidden = !ok;
                if (ok) visibles++;
            }
            for (var j = 0; j < deps.length; j++) {
                deps[j].classList.toggle('activo', !buscando && deps[j].getAttribute('data-region') === region);
            }
            contador.textContent = buscando
                ? plural(visibles, 'farmacia encontrada', 'farmacias encontradas')
                : plural(visibles, 'farmacia', 'farmacias') + ' en ' + region;
            vacio.hidden = visibles > 0;
        }

        function elegir(r) {
            region = r;
            select.value = r;
            input.value = '';
            aplicar();
        }

        input.addEventListener('input', aplicar);
        select.addEventListener('change', function () { elegir(select.value); });
        for (var k = 0; k < deps.length; k++) {
            deps[k].addEventListener('click', function () { elegir(this.getAttribute('data-region')); });
        }
    })();
    </script>
    <?php endif; ?>

    <script src="https://videoconsulta.iwtg.com/video-chat.umd.min.js"></script>
    <link rel="stylesheet" href="https://videoconsulta.iwtg.com/video-chat.css">
    <video-chat bottom right text-color="#ffffff" primary-color="#13a0b2" public-key="QXfeToaWrocXmZosEiwJlXcoy"></video-chat>
</body>

</html>
