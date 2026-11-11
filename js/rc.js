// Recetalia — JS común del sitio (sin jQuery): menú de celular, menú "Ingresar" y envío del
// formulario de contacto. Sin JS el menú queda visible (ver .rc-js en css/rc.css) y el formulario
// hace un POST normal a mailer/contact.php.
(function () {
    'use strict';

    var header = document.getElementById('rc-header');
    var hamburguesa = document.getElementById('rc-hamburguesa');
    var nav = document.getElementById('rc-nav');
    var ingresarBtn = document.getElementById('rc-ingresar-btn');
    var ingresarMenu = document.getElementById('rc-ingresar-menu');

    // ---- Menú "Ingresar" --------------------------------------------------------------------
    function ingresar(abierto, devolverFoco) {
        if (!ingresarBtn) return;
        ingresarBtn.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        ingresarMenu.classList.toggle('rc-abierto', abierto);
        if (!abierto && devolverFoco) ingresarBtn.focus();
    }
    if (ingresarBtn && ingresarMenu) {
        ingresarBtn.addEventListener('click', function () {
            var abrir = ingresarBtn.getAttribute('aria-expanded') !== 'true';
            ingresar(abrir);
            if (abrir) {
                var primero = ingresarMenu.querySelector('a');
                if (primero) primero.focus();
            }
        });
        // Flechas arriba/abajo dentro del menú.
        ingresarMenu.addEventListener('keydown', function (e) {
            if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
            var links = Array.prototype.slice.call(ingresarMenu.querySelectorAll('a'));
            var i = links.indexOf(document.activeElement);
            if (i === -1) return;
            e.preventDefault();
            links[(i + (e.key === 'ArrowDown' ? 1 : links.length - 1)) % links.length].focus();
        });
    }

    // ---- Menú de celular ------------------------------------------------------------------
    function menu(abierto, devolverFoco) {
        if (!hamburguesa || !header) return;
        header.classList.toggle('rc-menu-abierto', abierto);
        hamburguesa.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        if (!abierto) {
            ingresar(false);
            if (devolverFoco) hamburguesa.focus();
        }
    }
    if (hamburguesa && nav) {
        hamburguesa.addEventListener('click', function () {
            menu(hamburguesa.getAttribute('aria-expanded') !== 'true');
        });
        // Un link del menú (p. ej. #ContactSet en la portada) cierra el panel.
        nav.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('.rc-nav-link')) menu(false);
        });
        // Al pasar a escritorio el panel no aplica: se cierra para no quedar en estado raro.
        if (window.matchMedia) {
            var mq = window.matchMedia('(min-width: 1024px)');
            var alCambiar = function () { if (mq.matches) menu(false); };
            if (mq.addEventListener) mq.addEventListener('change', alCambiar); else if (mq.addListener) mq.addListener(alCambiar);
        }
    }

    // Escape cierra lo que esté abierto (primero "Ingresar", después el menú).
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' && e.key !== 'Esc') return;
        if (ingresarBtn && ingresarBtn.getAttribute('aria-expanded') === 'true') { ingresar(false, true); return; }
        if (hamburguesa && hamburguesa.getAttribute('aria-expanded') === 'true') menu(false, true);
    });
    // Click afuera cierra.
    document.addEventListener('click', function (e) {
        if (ingresarBtn && ingresarBtn.getAttribute('aria-expanded') === 'true'
            && !ingresarMenu.contains(e.target) && !ingresarBtn.contains(e.target)) ingresar(false);
        if (header && header.classList.contains('rc-menu-abierto') && !header.contains(e.target)) menu(false);
    });

    // ---- Formulario de contacto -------------------------------------------------------------
    // Contrato con mailer/contact.php (el mismo que tenía js/custom.js): POST urlencoded de
    // name, email, comment, ft y website; la respuesta es HTML que se muestra en #message.
    var form = document.querySelector('.about-contact-form');
    if (form && window.fetch && window.URLSearchParams) {
        var mensaje = document.getElementById('message');
        var boton = document.getElementById('submit');
        var valor = function (id) { var c = document.getElementById(id); return c ? c.value : ''; };
        var avisar = function (html) { mensaje.innerHTML = html; };

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (window.grecaptcha && typeof window.grecaptcha.getResponse === 'function'
                && document.getElementById('html_element') && document.getElementById('html_element').children.length
                && window.grecaptcha.getResponse() === '') {
                avisar('<div class="alert alert-error">Marcá la casilla de verificación «No soy un robot».</div>');
                return;
            }
            var datos = new URLSearchParams();
            datos.append('name', valor('name'));
            datos.append('email', valor('email'));
            datos.append('comment', valor('comment'));
            datos.append('ft', valor('ft'));
            datos.append('website', valor('website'));

            avisar('');
            boton.disabled = true;
            boton.setAttribute('aria-busy', 'true');
            var giro = document.createElement('span');
            giro.className = 'rc-cargando';
            giro.setAttribute('aria-hidden', 'true');
            boton.insertBefore(giro, boton.firstChild);
            var icono = boton.querySelector('svg');
            if (icono) icono.style.display = 'none';

            fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' },
                body: datos.toString()
            })
                .then(function (r) { return r.text(); })
                .then(function (html) { avisar(html); })
                .catch(function () { avisar('<div class="alert alert-error">No pudimos enviar el mensaje. Probá de nuevo en unos minutos.</div>'); })
                .then(function () {
                    boton.disabled = false;
                    boton.removeAttribute('aria-busy');
                    if (giro.parentNode) giro.parentNode.removeChild(giro);
                    if (icono) icono.style.display = '';
                });
        });
    }
})();
