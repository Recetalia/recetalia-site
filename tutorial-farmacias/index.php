<?php
// Tutorial para farmacias: video, PDF y formulario de contacto. Layout común: inc/layout.php.
require_once __DIR__ . '/../inc/layout.php';
rc_head(array(
    'title' => 'Tutorial Farmacias - Recetalia - Receta Digital',
    'description' => 'Tutorial para farmacias: cómo dispensar recetas digitales de Recetalia. Video y guía en PDF.',
    'canonical' => rtrim(getenv('SITE_PUBLIC_URL') ?: 'https://recetalia.com', '/') . '/tutorial-farmacias/',
    'recaptcha' => true,
));
?>
<main id="contenido">
    <section class="rc-pagina-cabeza" aria-labelledby="pagina-titulo">
        <div class="rc-container rc-angosto">
            <nav class="rc-migas" aria-label="Ruta de navegación">
                <ol>
                    <li><a href="/">Inicio</a><?php echo rc_icono('chevron-right'); ?></li>
                    <li><span aria-current="page">Tutorial farmacias</span></li>
                </ol>
            </nav>
            <h1 id="pagina-titulo">Tutorial farmacias</h1>
        </div>
    </section>
    <section class="rc-seccion rc-seccion-pegada">
        <div class="rc-container rc-angosto">
            <div class="rc-video">
                <video controls preload="metadata" playsinline src="/videotutoriales/demo.mp4">
                    <a href="/videotutoriales/demo.mp4">Ver el video del tutorial</a>
                </video>
            </div>
            <p class="rc-btn-fila rc-centrado">
                <a href="/tutorial-farmacias.pdf" target="_blank" rel="noopener" class="rc-btn rc-btn-primario rc-btn-grande"><?php echo rc_icono('download'); ?>Descargar PDF<span class="rc-sr"> (abre en otra pestaña)</span></a>
            </p>
        </div>
    </section>
    <?php rc_contacto(); ?>
</main>
<?php rc_pie('', true); ?>
