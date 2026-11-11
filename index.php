<?php
// Portada del sitio institucional. Layout común: inc/layout.php (header/footer compartidos).
require_once __DIR__ . '/inc/layout.php';

rc_head(array(
    'title' => 'Recetalia - Receta Digital',
    'description' => 'RECETALIA es la Plataforma de prescripción de órdenes médicas en formato electrónico.',
    'canonical' => rtrim(getenv('SITE_PUBLIC_URL') ?: 'https://recetalia.com', '/') . '/',
    'recaptcha' => true,
    'head_extra' => '<link rel="preload" as="image" href="/images/hero-farmacias-uy.webp" type="image/webp">',
));
?>
<main id="contenido">
    <section class="rc-hero" aria-labelledby="hero-titulo">
        <div class="rc-container rc-hero-grilla">
            <div class="rc-hero-texto">
                <p class="rc-chip"><?php echo rc_icono('shield-check'); ?>Receta digital en Uruguay</p>
                <h1 id="hero-titulo">Receta <span>digital</span>.</h1>
                <p class="rc-bajada">RECETALIA es la Plataforma de prescripción de órdenes médicas en formato electrónico.</p>
                <div class="rc-hero-ctas">
                    <a class="rc-btn rc-btn-primario rc-btn-grande" href="https://medicos.recetalia.com/login" target="_blank" rel="noopener"><?php echo rc_icono('stethoscope'); ?>Soy médico<span class="rc-sr"> (abre en otra pestaña)</span></a>
                    <a class="rc-btn rc-btn-secundario rc-btn-grande" href="https://farmacias.recetalia.com/login" target="_blank" rel="noopener"><?php echo rc_icono('store'); ?>Soy farmacia<span class="rc-sr"> (abre en otra pestaña)</span></a>
                </div>
                <p class="rc-hero-pacientes">¿Sos paciente? <a href="/medicamentos/">Consultá tus medicamentos<?php echo rc_icono('arrow-right'); ?></a></p>
            </div>
            <div class="rc-hero-visual">
                <picture>
                    <source srcset="/images/hero-farmacias-uy.webp" type="image/webp">
                    <img src="/images/hero-farmacias-uy.jpg" width="600" height="640" alt="Mapa de Uruguay con farmacias marcadas en todo el país">
                </picture>
                <div class="rc-hero-dato">
                    <span class="rc-tarjeta-icono"><?php echo rc_icono('store'); ?></span>
                    <div>
                        <strong>+600 farmacias</strong>
                        <span>Registradas en todo el país.</span>
                        <a href="/farmacias-habilitadas.php">Ver farmacias habilitadas</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rc-seccion" aria-labelledby="como-titulo">
        <div class="rc-container">
            <div class="rc-seccion-cabeza">
                <p class="rc-sobretitulo">¿Cómo funciona?</p>
                <h2 id="como-titulo">Es un proceso rápido y sencillo</h2>
            </div>
            <ol class="rc-grilla rc-grilla-3 rc-pasos" role="list">
                <li class="rc-tarjeta rc-paso">
                    <span class="rc-paso-num" aria-hidden="true">01</span>
                    <span class="rc-tarjeta-icono"><?php echo rc_icono('file-text'); ?></span>
                    <h3>Paso 1</h3>
                    <p>El médico emite la receta digital y se genera un documento único con código de identificación.</p>
                </li>
                <li class="rc-tarjeta rc-paso">
                    <span class="rc-paso-num" aria-hidden="true">02</span>
                    <span class="rc-tarjeta-icono"><?php echo rc_icono('store'); ?></span>
                    <h3>Paso 2</h3>
                    <p>Las farmacias acceden a las recetas digitales activas y el paciente recibe una copia.</p>
                </li>
                <li class="rc-tarjeta rc-paso">
                    <span class="rc-paso-num" aria-hidden="true">03</span>
                    <span class="rc-tarjeta-icono"><?php echo rc_icono('smartphone'); ?></span>
                    <h3>Paso 3</h3>
                    <p>El paciente elige la farmacia de su preferencia, retira y/o recibe la medicación solo con el código de la receta.</p>
                </li>
            </ol>
        </div>
    </section>

    <section class="rc-seccion rc-seccion-fondo" aria-labelledby="beneficios-titulo">
        <div class="rc-container">
            <div class="rc-seccion-cabeza">
                <p class="rc-sobretitulo">Principales beneficios</p>
                <h2 id="beneficios-titulo">Optimizamos el proceso de prescripción y creamos valor para todas las partes</h2>
            </div>
            <div class="rc-grilla rc-grilla-2">
                <article class="rc-tarjeta rc-beneficio">
                    <h3><span class="rc-tarjeta-icono"><?php echo rc_icono('stethoscope'); ?></span>Para los médicos</h3>
                    <ul class="rc-checks">
                        <li><?php echo rc_icono('check'); ?>Agiliza la forma de recetar, permite utilizar indicaciones anteriores y repetir la medicación crónica de forma remota.</li>
                        <li><?php echo rc_icono('check'); ?>Puede saber si el paciente retira la medicación y darle seguimiento al tratamiento.</li>
                        <li><?php echo rc_icono('check'); ?>Certeza de que el documento no se altera, ni se emiten recetas en su nombre.</li>
                        <li><?php echo rc_icono('check'); ?>Control detallado de su historial de recetas.</li>
                    </ul>
                </article>
                <article class="rc-tarjeta rc-beneficio">
                    <h3><span class="rc-tarjeta-icono"><?php echo rc_icono('store'); ?></span>Para las farmacias</h3>
                    <ul class="rc-checks">
                        <li><?php echo rc_icono('check'); ?>Mayor eficiencia operativa.</li>
                        <li><?php echo rc_icono('check'); ?>Certeza y seguridad en la prescripción médica.</li>
                        <li><?php echo rc_icono('check'); ?>Democratización de la oferta de cara al usuario.</li>
                        <li><?php echo rc_icono('check'); ?>Nuevo canal de ventas o dispensación (Open Market).</li>
                        <li><?php echo rc_icono('check'); ?>Mejora su propuesta de servicio y aumenta la satisfacción del usuario.</li>
                    </ul>
                </article>
                <article class="rc-tarjeta rc-beneficio">
                    <h3><span class="rc-tarjeta-icono"><?php echo rc_icono('user'); ?></span>Para los usuarios</h3>
                    <ul class="rc-checks">
                        <li><?php echo rc_icono('check'); ?>Comprensión asegurada de las indicaciones médicas, posología y tipo de droga.</li>
                        <li><?php echo rc_icono('check'); ?>No necesita desplazarse para obtener la receta.</li>
                        <li><?php echo rc_icono('check'); ?>El paciente accede desde su smartphone al detalle de la medicación y posología.</li>
                        <li><?php echo rc_icono('check'); ?>Le permite además asociar recordatorios de consumo y consultar su historial de prescripciones.</li>
                        <li><?php echo rc_icono('check'); ?>Repetición inmediata de la medicación, sin necesidad de ir a una consulta.</li>
                        <li><?php echo rc_icono('check'); ?>Acceso a la mejor oferta de servicios y precios de toda la red de farmacia.</li>
                    </ul>
                </article>
                <article class="rc-tarjeta rc-beneficio">
                    <h3><span class="rc-tarjeta-icono"><?php echo rc_icono('building'); ?></span>Para el sector</h3>
                    <ul class="rc-checks">
                        <li><?php echo rc_icono('check'); ?>Optimización del proceso de prescripción, haciéndolo más eficiente.</li>
                        <li><?php echo rc_icono('check'); ?>Auditoría más robusta, en términos de trazabilidad y fidelidad de la información.</li>
                        <li><?php echo rc_icono('check'); ?>Validación automática de los datos del médico y el paciente.</li>
                        <li><?php echo rc_icono('check'); ?>Sustentable y amigable con el medio ambiente, reduciendo el uso innecesario de papel.</li>
                        <li><?php echo rc_icono('check'); ?>Información detallada en tiempo real, estructurada para el análisis y toma de decisiones.</li>
                        <li><?php echo rc_icono('check'); ?>Incentivo para incrementar la calidad de servicio de las farmacias.</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="rc-seccion" aria-labelledby="medicamentos-titulo">
        <div class="rc-container">
            <div class="rc-seccion-cabeza">
                <p class="rc-sobretitulo">Medicamentos</p>
                <h2 id="medicamentos-titulo">Información de medicamentos para médicos y pacientes</h2>
            </div>
            <div class="rc-grilla rc-grilla-2">
                <section class="rc-tarjeta rc-med-tarjeta" aria-labelledby="med-medicos">
                    <span class="rc-tarjeta-icono"><?php echo rc_icono('pill'); ?></span>
                    <p class="rc-sobretitulo">Médicos</p>
                    <h3 id="med-medicos">Información técnica de medicamentos</h3>
                    <p class="rc-bajada">Buscá cualquier medicamento registrado en Uruguay y consultá su ficha técnica de referencia: indicaciones, posología, contraindicaciones e interacciones.</p>
                    <form action="/medicamentos/" method="get" role="search">
                        <label class="rc-label" for="portada-q">Nombre o principio activo</label>
                        <div class="rc-med-fila">
                            <div class="rc-buscador">
                                <?php echo rc_icono('search'); ?>
                                <input class="rc-input" id="portada-q" name="q" type="search" placeholder="Ej.: perifar o paracetamol" minlength="2" maxlength="80" autocomplete="off">
                            </div>
                            <button type="submit" class="rc-btn rc-btn-primario">Buscar</button>
                        </div>
                    </form>
                </section>
                <section class="rc-tarjeta rc-med-tarjeta rc-med-tarjeta-pacientes" aria-labelledby="med-pacientes">
                    <span class="rc-tarjeta-icono"><?php echo rc_icono('heart-pulse'); ?></span>
                    <p class="rc-sobretitulo">Pacientes</p>
                    <h3 id="med-pacientes">Mis medicamentos</h3>
                    <p class="rc-bajada">¿Tomás más de un medicamento? Revisá si se llevan bien entre sí y con tu situación (edad, embarazo, alergias, enfermedades), explicado en lenguaje simple.</p>
                    <p><a class="rc-btn rc-btn-primario" href="/medicamentos/interacciones/"><?php echo rc_icono('list-check'); ?>Revisar mis medicamentos</a></p>
                </section>
            </div>
        </div>
    </section>

    <section class="rc-seccion rc-seccion-banda" aria-labelledby="farmacias-titulo">
        <div class="rc-container">
            <div class="rc-banda">
                <div>
                    <h2 id="farmacias-titulo">Farmacias en todo el país</h2>
                    <p>Pedile Recetalia a tu médico.</p>
                </div>
                <a class="rc-btn rc-btn-primario rc-btn-grande" href="/farmacias-habilitadas.php"><?php echo rc_icono('map-pin'); ?>Ver farmacias habilitadas</a>
            </div>
        </div>
    </section>

    <?php rc_contacto(); ?>
</main>
<?php rc_pie('', true); ?>
