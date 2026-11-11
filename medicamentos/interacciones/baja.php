<?php
// Borrado de los datos del verificador (Ley 18.331: supresión). GET muestra la confirmación; el borrado
// es por POST para que un antivirus o vista previa del email que abra el link no borre nada.
require_once __DIR__ . '/../../inc/verificador-db.php';
require_once __DIR__ . '/../../inc/vademecum-layout.php';

$token = isset($_REQUEST['t']) ? (string)$_REQUEST['t'] : '';
$estado = 'confirmar';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $estado = vr_baja(vr_pdo(), $token) ? 'borrado' : 'inexistente';
    } catch (PDOException $e) {
        error_log('verificador baja: ' . $e->getMessage());
        $estado = 'error';
    }
}

header('Cache-Control: no-store');
vd_head(array('title' => 'Borrar mis datos | Recetalia', 'description' => 'Borrar los datos del verificador de interacciones.',
    'canonical' => '/medicamentos/interacciones/baja', 'robots' => 'noindex'));
?>
<main id="contenido" class="vd-main"><div class="container vd-contenedor vd-404">
    <h1 class="vd-h1">Borrar mis datos</h1>
    <?php if ($estado === 'confirmar'): ?>
        <p>Vamos a borrar tu email, tus datos y todas las consultas que hiciste en el verificador de interacciones. No se puede deshacer.</p>
        <form method="post" action="/medicamentos/interacciones/baja">
            <input type="hidden" name="t" value="<?php echo vd_e($token); ?>">
            <button type="submit" class="vd-boton">Borrar mis datos</button>
        </form>
    <?php elseif ($estado === 'borrado'): ?>
        <p class="vd-estado" role="status">Listo: borramos tus datos y tus consultas.</p>
    <?php elseif ($estado === 'inexistente'): ?>
        <p class="vd-estado" role="status">No encontramos datos para este enlace. Puede que ya los hayas borrado.</p>
    <?php else: ?>
        <p class="vd-estado" role="alert">No pudimos borrar tus datos en este momento. Probá de nuevo en unos minutos.</p>
    <?php endif; ?>
</div></main>
<?php vd_pie(); ?>
