<?php
// Footer común. Se incluye desde rc_pie() en inc/layout.php.
require_once __DIR__ . '/inc/layout.php';
?>
    <footer class="rc-footer">
        <div class="rc-container rc-footer-grilla">
            <div class="rc-footer-marca">
                <a href="/" class="rc-footer-logo"><img src="/images/footer-brand.png" alt="Recetalia: inicio" width="154" height="48" loading="lazy"></a>
                <p>Plataforma de prescripción de órdenes médicas en formato electrónico.</p>
                <ul class="rc-footer-datos">
                    <li><?php echo rc_icono('mail'); ?><a href="mailto:hello@recetalia.com">hello@recetalia.com</a></li>
                    <li><?php echo rc_icono('map-pin'); ?><span>WTC Montevideo, Torre III, Piso 12.</span></li>
                </ul>
            </div>
            <nav class="rc-footer-col" aria-label="Sitio">
                <h2 class="rc-footer-titulo">Sitio</h2>
                <ul>
                    <li><a href="/">Inicio</a></li>
                    <li><a href="/medicamentos/">Medicamentos</a></li>
                    <li><a href="/medicamentos/interacciones/">Interacciones entre medicamentos</a></li>
                    <li><a href="/farmacias-habilitadas.php">Farmacias habilitadas</a></li>
                    <li><a href="/tutorial-farmacias/">Tutorial para farmacias</a></li>
                </ul>
            </nav>
            <nav class="rc-footer-col" aria-label="Ingresar">
                <h2 class="rc-footer-titulo">Ingresar</h2>
                <ul>
                    <li><a href="https://medicos.recetalia.com/login" target="_blank" rel="noopener">Médicos<span class="rc-sr"> (abre en otra pestaña)</span></a></li>
                    <li><a href="https://farmacias.recetalia.com/login" target="_blank" rel="noopener">Farmacias<span class="rc-sr"> (abre en otra pestaña)</span></a></li>
                    <li><a href="https://prestadores.recetalia.com/login" target="_blank" rel="noopener">Prestadores<span class="rc-sr"> (abre en otra pestaña)</span></a></li>
                </ul>
            </nav>
            <nav class="rc-footer-col" aria-label="Legal">
                <h2 class="rc-footer-titulo">Legal</h2>
                <ul>
                    <li><a href="/terms/">Términos y condiciones</a></li>
                    <li><a href="/privacy/">Política de privacidad</a></li>
                </ul>
            </nav>
        </div>
        <div class="rc-footer-base">
            <div class="rc-container rc-footer-base-fila">
                <p>&copy; <?php echo date('Y'); ?> Recetalia. Todos los derechos reservados.</p>
                <a href="https://www.iwtg.com/" class="rc-footer-iwtg" target="_blank" rel="noopener"><img src="/iwtg/powered-by-iwtg.png" alt="Powered by IWTG" width="132" height="22" loading="lazy"><span class="rc-sr"> (abre en otra pestaña)</span></a>
            </div>
        </div>
    </footer>
