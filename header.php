<?php
// Header común (sticky). Se incluye desde rc_head() en inc/layout.php. Menú de celular y de
// "Ingresar": js/rc.js (sin jQuery). Sin JS el menú queda siempre visible (clase rc-js en <html>).
require_once __DIR__ . '/inc/layout.php';
$rcSeccion = rc_seccion();
// Fuera de la portada el ancla tiene que llevar a la portada.
$rcContacto = $rcSeccion === 'inicio' ? '#ContactSet' : '/#ContactSet';
$rcActual = function ($s) use ($rcSeccion) { return $rcSeccion === $s ? ' aria-current="page"' : ''; };
?>
    <header class="rc-header" id="rc-header">
        <div class="rc-container rc-header-fila">
            <a class="rc-logo" href="/"><img src="/images/header-brand.png" alt="Recetalia, receta digital: inicio" width="154" height="48"></a>
            <button type="button" class="rc-hamburguesa" id="rc-hamburguesa" aria-expanded="false" aria-controls="rc-nav">
                <span class="rc-hamburguesa-abrir"><?php echo rc_icono('menu'); ?></span>
                <span class="rc-hamburguesa-cerrar"><?php echo rc_icono('x'); ?></span>
                <span class="rc-sr">Menú</span>
            </button>
            <nav class="rc-nav" id="rc-nav" aria-label="Principal">
                <ul class="rc-nav-lista">
                    <li><a class="rc-nav-link" href="/"<?php echo $rcActual('inicio'); ?>>Inicio</a></li>
                    <li><a class="rc-nav-link" href="/medicamentos/"<?php echo $rcActual('medicamentos'); ?>>Medicamentos</a></li>
                    <li><a class="rc-nav-link" href="/farmacias-habilitadas.php"<?php echo $rcActual('farmacias'); ?>>Farmacias habilitadas</a></li>
                    <li><a class="rc-nav-link" href="<?php echo $rcContacto; ?>">Contacto</a></li>
                </ul>
                <div class="rc-ingresar">
                    <button type="button" class="rc-btn rc-btn-primario rc-ingresar-btn" id="rc-ingresar-btn" aria-expanded="false" aria-controls="rc-ingresar-menu">
                        <?php echo rc_icono('log-in'); ?>Ingresar<?php echo rc_icono('chevron-down', 'rc-icono rc-ingresar-flecha'); ?>
                    </button>
                    <ul class="rc-ingresar-menu" id="rc-ingresar-menu" aria-label="Ingresar como">
                        <li><a href="https://medicos.recetalia.com/login" target="_blank" rel="noopener"><?php echo rc_icono('stethoscope'); ?><span>Médicos</span><?php echo rc_icono('external', 'rc-icono rc-icono-chico'); ?><span class="rc-sr"> (abre en otra pestaña)</span></a></li>
                        <li><a href="https://farmacias.recetalia.com/login" target="_blank" rel="noopener"><?php echo rc_icono('store'); ?><span>Farmacias</span><?php echo rc_icono('external', 'rc-icono rc-icono-chico'); ?><span class="rc-sr"> (abre en otra pestaña)</span></a></li>
                        <li><a href="https://prestadores.recetalia.com/login" target="_blank" rel="noopener"><?php echo rc_icono('building'); ?><span>Prestadores</span><?php echo rc_icono('external', 'rc-icono rc-icono-chico'); ?><span class="rc-sr"> (abre en otra pestaña)</span></a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>
