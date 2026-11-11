<?php
// Verificador de interacciones para pacientes. GET: formulario (lista de medicamentos + datos).
// POST: valida, consulta a Consilio, guarda perfil y consulta (schema `publico`), envía la copia por
// email y muestra el resultado. Spec: doc/plans/2026-11-11-verificador-pacientes-design.md.
require_once __DIR__ . '/../../inc/verificador-db.php';
require_once __DIR__ . '/../../inc/verificador-consilio.php';
require_once __DIR__ . '/../../inc/verificador-email.php';
require_once __DIR__ . '/../../inc/vademecum-layout.php';
require_once __DIR__ . '/../../inc/antispam.php';

const VR_POR_HORA = 10;

// Honeypot + ficha firmada (inc/antispam.php) + límite propio por IP (el del formulario de contacto es 3/h).
function vr_antispam_motivo() {
    if (trim(isset($_POST['website']) ? $_POST['website'] : '') !== '') return 'campo-trampa';
    $partes = explode('.', isset($_POST['ft']) ? $_POST['ft'] : '', 2);
    if (count($partes) !== 2 || !ctype_digit($partes[0])
        || !hash_equals(hash_hmac('sha256', $partes[0], antispam_secreto()), $partes[1])) return 'ficha';
    $edad = time() - (int)$partes[0];
    if ($edad < 5 || $edad > 86400) return 'tiempo';
    $dir = sys_get_temp_dir() . '/recetalia-verificador-rl';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $f = $dir . '/' . hash('sha256', antispam_ip());
    $ahora = time();
    $previos = is_file($f) ? array_filter(array_map('intval', file($f, FILE_IGNORE_NEW_LINES)), function ($x) use ($ahora) { return $x > $ahora - 3600; }) : array();
    if (count($previos) >= VR_POR_HORA) return 'limite-ip';
    $previos[] = $ahora;
    file_put_contents($f, implode("\n", $previos), LOCK_EX);
    return null;
}

$errores = array();
$datos = array('edad' => '', 'sexo' => '', 'embarazo' => false, 'lactancia' => false, 'alergias' => array(),
    'patologias' => array(), 'email' => '', 'novedades' => false);
$productos = array();
$resultado = null;      // ['alertas', 'resumen', 'email_enviado']
$falla = null;          // mensaje general

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v = vr_validar($_POST);
    $errores = $v['errores'];
    $datos = $v['datos'];
    try {
        $pdo = vr_pdo();
        $productos = vr_resolver_productos($pdo, $datos['productos']);
        if (!$errores && count($productos) < 2) {
            $errores['productos'] = 'Elegí al menos dos medicamentos de la lista.';
        }
        if (!$errores) {
            $motivo = vr_antispam_motivo();
            if ($motivo !== null) {
                error_log('verificador: antispam motivo=' . $motivo);
                $falla = $motivo === 'limite-ip'
                    ? 'Hiciste muchas consultas en poco tiempo. Probá de nuevo en una hora.'
                    : 'No pudimos procesar el formulario. Recargá la página y probá de nuevo.';
            }
        }
        if (!$errores && !$falla) {
            $respuesta = vr_consultar($productos, $datos);
            if ($respuesta === null) {
                $falla = 'El servicio de interacciones no está disponible en este momento. Probá de nuevo en unos minutos.';
            } else {
                $alertas = vr_alertas($productos, $respuesta[0], $respuesta[1]);
                $resumen = vr_resumen($alertas);
                list($personaId, $token) = vr_guardar_persona($pdo, $datos['email'], $datos['novedades']);
                vr_guardar_consulta($pdo, $personaId, $datos, $productos, $resumen);
                $enviado = vr_enviar_resultado($datos['email'], $productos, $alertas, $token);
                $resultado = array('alertas' => $alertas, 'resumen' => $resumen, 'email_enviado' => $enviado);
            }
        }
        $pdo = null;
    } catch (PDOException $e) {
        error_log('verificador: ' . $e->getMessage());
        $falla = 'No pudimos procesar tu consulta en este momento. Probá de nuevo en unos minutos.';
    }
}

header('Cache-Control: no-store');
vd_head(array(
    'title' => 'Interacciones entre medicamentos: revisá los que tomás | Recetalia',
    'description' => 'Elegí los medicamentos que tomás, indicá algunos datos y conocé las posibles interacciones y advertencias, explicadas en lenguaje simple.',
    'canonical' => '/medicamentos/interacciones/',
    'robots' => $_SERVER['REQUEST_METHOD'] === 'POST' ? 'noindex' : null,
));
$err = function ($campo) use ($errores) {
    return isset($errores[$campo]) ? '<p class="vr-error" id="err-' . $campo . '">' . vd_e($errores[$campo]) . '</p>' : '';
};
?>
<main id="contenido" class="vd-main">
    <section class="vd-hero">
        <div class="container vd-contenedor">
            <nav class="vd-migas" aria-label="Ruta de navegación">
                <a href="/">Inicio</a> <span aria-hidden="true">›</span>
                <a href="/medicamentos/">Medicamentos</a> <span aria-hidden="true">›</span>
                <span aria-current="page">Interacciones</span>
            </nav>
            <h1 class="vd-h1">¿Los medicamentos que tomás se llevan bien?</h1>
            <p class="vd-bajada">Elegí los medicamentos que tomás, contanos algunos datos y te mostramos las posibles interacciones y advertencias, explicadas en lenguaje simple. Te mandamos una copia por email.</p>
            <?php if (!VR_MENSAJES_APROBADOS): ?><p class="vr-borrador">Borrador: los textos para pacientes están pendientes de revisión médica.</p><?php endif; ?>
        </div>
    </section>

    <div class="container vd-contenedor">
    <?php if ($resultado): ?>
        <section class="vr-resultado" aria-labelledby="vr-res-titulo">
            <h2 id="vr-res-titulo" class="vd-h2"><?php echo vd_e(vr_titulo_resumen($resultado['resumen'])); ?></h2>
            <p class="vr-revisados">Medicamentos revisados: <?php echo vd_e(implode(', ', array_map(function ($p) { return $p['nombre']; }, $productos))); ?>.</p>
            <?php if ($resultado['alertas']): ?>
                <?php echo vr_html_alertas($resultado['alertas']); ?>
            <?php else: ?>
                <p class="vd-estado"><?php echo vd_e(VR_SIN_HALLAZGOS); ?></p>
            <?php endif; ?>
            <p class="vr-email-estado" role="status"><?php echo $resultado['email_enviado']
                ? 'Te mandamos una copia a ' . vd_e($datos['email']) . '.'
                : 'No pudimos mandarte la copia por email. Podés guardar o imprimir esta página.'; ?></p>
            <aside class="vd-fuente" aria-label="Aviso y fuentes">
                <p class="vd-aviso"><?php echo vd_e(VR_AVISO); ?></p>
                <p><?php echo vd_e(vr_fuentes_usadas($resultado['alertas'])); ?></p>
            </aside>
            <p><a class="vd-boton" href="/medicamentos/interacciones/">Hacer otra consulta</a></p>
        </section>
    <?php else: ?>
        <?php if ($falla): ?><p class="vd-estado vr-falla" role="alert"><?php echo vd_e($falla); ?></p><?php endif; ?>
        <?php if ($errores): ?><p class="vd-estado vr-falla" role="alert">Revisá los datos marcados abajo.</p><?php endif; ?>
        <noscript><p class="vd-estado">Para armar tu lista de medicamentos necesitás tener JavaScript habilitado.</p></noscript>

        <form id="vr-form" class="vr-form" action="/medicamentos/interacciones/" method="post" novalidate>
            <?php echo antispam_campos(); ?>
            <fieldset class="vr-paso">
                <legend class="vd-h2">1. Tus medicamentos</legend>
                <label for="vr-buscar" class="vd-etiqueta">Buscá y agregá los medicamentos que tomás (al menos 2)</label>
                <div class="vd-buscador-fila">
                    <input id="vr-buscar" type="search" autocomplete="off" placeholder="Ej.: perifar o paracetamol" aria-controls="vr-sugerencias">
                </div>
                <ul id="vr-sugerencias" class="vd-lista vr-sugerencias" aria-live="polite"></ul>
                <h3 class="vd-h3 vr-sub">Tu lista</h3>
                <ul id="vr-lista" class="vr-lista" data-inicial="<?php echo vd_e(json_encode(array_map(function ($p) {
                    return array('id' => $p['id'], 'nombre' => $p['nombre'], 'detalle' => $p['generico']); }, $productos), JSON_UNESCAPED_UNICODE)); ?>"></ul>
                <p id="vr-lista-vacia" class="vr-ayuda">Todavía no agregaste medicamentos.</p>
                <?php echo $err('productos'); ?>
            </fieldset>

            <fieldset class="vr-paso">
                <legend class="vd-h2">2. Tus datos</legend>
                <div class="vr-fila">
                    <div class="vr-campo">
                        <label for="vr-edad" class="vd-etiqueta">Edad</label>
                        <input id="vr-edad" name="edad" type="number" inputmode="numeric" min="0" max="120" required value="<?php echo vd_e($datos['edad']); ?>"<?php echo isset($errores['edad']) ? ' aria-invalid="true" aria-describedby="err-edad"' : ''; ?>>
                        <?php echo $err('edad'); ?>
                    </div>
                    <div class="vr-campo">
                        <span class="vd-etiqueta" id="vr-sexo-et">Sexo</span>
                        <div class="vr-opciones" role="radiogroup" aria-labelledby="vr-sexo-et">
                            <label><input type="radio" name="sexo" value="F"<?php echo $datos['sexo'] === 'F' ? ' checked' : ''; ?>> Femenino</label>
                            <label><input type="radio" name="sexo" value="M"<?php echo $datos['sexo'] === 'M' ? ' checked' : ''; ?>> Masculino</label>
                        </div>
                        <?php echo $err('sexo'); ?>
                    </div>
                </div>
                <div id="vr-femenino" class="vr-campo">
                    <span class="vd-etiqueta">Embarazo y lactancia</span>
                    <div class="vr-opciones vr-checks">
                        <label><input type="checkbox" name="embarazo" value="1"<?php echo $datos['embarazo'] ? ' checked' : ''; ?>> Estoy embarazada</label>
                        <label><input type="checkbox" name="lactancia" value="1"<?php echo $datos['lactancia'] ? ' checked' : ''; ?>> Estoy amamantando</label>
                    </div>
                    <p class="vr-ayuda" id="vr-femenino-ayuda">Se habilita al elegir sexo femenino.</p>
                </div>
                <div class="vr-campo">
                    <label for="vr-alergias" class="vd-etiqueta">Alergias a medicamentos <span class="vr-opcional">(opcional)</span></label>
                    <input id="vr-alergias" type="search" autocomplete="off" placeholder="Ej.: amoxicilina, ibuprofeno" aria-controls="vr-alergias-sug">
                    <ul id="vr-alergias-sug" class="vd-lista vr-sugerencias"></ul>
                    <ul id="vr-alergias-lista" class="vr-lista" data-inicial="<?php echo vd_e(json_encode(array_map(function ($a) {
                        return array('nombre' => $a, 'mostrar' => $a); }, $datos['alergias']), JSON_UNESCAPED_UNICODE)); ?>"></ul>
                    <p class="vr-ayuda">Elegí el medicamento de la lista que aparece al escribir.</p>
                </div>
                <div class="vr-campo">
                    <label for="vr-enf" class="vd-etiqueta">Enfermedades que tenés <span class="vr-opcional">(opcional)</span></label>
                    <input id="vr-enf" type="search" autocomplete="off" placeholder="Ej.: diabetes, hipertensión" aria-controls="vr-enf-sug">
                    <ul id="vr-enf-sug" class="vd-lista vr-sugerencias"></ul>
                    <ul id="vr-enf-lista" class="vr-lista" data-inicial="<?php echo vd_e(json_encode($datos['patologias'], JSON_UNESCAPED_UNICODE)); ?>"></ul>
                    <?php echo $err('patologias'); ?>
                </div>
                <div class="vr-campo">
                    <label for="vr-email" class="vd-etiqueta">Email</label>
                    <input id="vr-email" name="email" type="email" autocomplete="email" required maxlength="200" value="<?php echo vd_e($datos['email']); ?>"<?php echo isset($errores['email']) ? ' aria-invalid="true" aria-describedby="err-email"' : ''; ?>>
                    <p class="vr-ayuda">Te mandamos una copia del resultado.</p>
                    <?php echo $err('email'); ?>
                </div>
            </fieldset>
            <div class="vr-aviso-caja" role="note">
                <p><?php echo vd_e(VR_AVISO); ?></p>
            </div>
            <div class="vr-consentimiento">
                <label><input type="checkbox" name="consentimiento" value="1" required> Acepto que Recetalia guarde estos datos (incluidos datos de salud) para darme el resultado y enviármelo por email, según la <a href="/privacy/" target="_blank" rel="noopener">política de privacidad</a>. Puedo pedir que los borren en cualquier momento.</label>
                <?php echo $err('consentimiento'); ?>
                <label><input type="checkbox" name="novedades" value="1"<?php echo $datos['novedades'] ? ' checked' : ''; ?>> Quiero recibir novedades de Recetalia por email (opcional).</label>
            </div>
            <button type="submit" class="vd-boton vr-enviar">Ver interacciones</button>
        </form>
    <?php endif; ?>
    </div>
</main>
<?php vd_pie('<script src="/js/verificador.js?v=3" defer></script>'); ?>
