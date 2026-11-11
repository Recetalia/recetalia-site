// Vademécum: búsqueda mientras se escribe (/medicamentos/buscar) y "Ver más" en las secciones de la ficha.
// Sin JS todo funciona: el formulario manda ?q= y las secciones se ven completas.
(function () {
    'use strict';

    // El menú y el header los maneja js/rc.js (layout común).

    // ---- Secciones plegables -------------------------------------------------------------
    // Se pliegan sólo si el texto supera el alto de ~5 líneas; si no, no hace falta el botón.
    var plegables = document.querySelectorAll('[data-plegable]');
    Array.prototype.forEach.call(plegables, function (el, i) {
        el.classList.add('vd-plegado');
        if (el.scrollHeight <= el.clientHeight + 2 * parseFloat(getComputedStyle(el).lineHeight || 28)) {
            el.classList.remove('vd-plegado');
            return;
        }
        if (!el.id) el.id = 'vd-texto-' + i;
        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'vd-vermas';
        boton.setAttribute('aria-controls', el.id);
        boton.setAttribute('aria-expanded', 'false');
        boton.textContent = 'Ver más';
        boton.addEventListener('click', function () {
            var abierto = el.classList.toggle('vd-plegado') === false;
            boton.setAttribute('aria-expanded', abierto ? 'true' : 'false');
            boton.textContent = abierto ? 'Ver menos' : 'Ver más';
        });
        el.parentNode.insertBefore(boton, el.nextSibling);
    });

    // ---- Búsqueda en vivo ----------------------------------------------------------------
    var input = document.getElementById('vd-q');
    var lista = document.getElementById('vd-resultados');
    var contador = document.getElementById('vd-contador');
    if (!input || !lista || !window.fetch) return;

    var temporizador = null;
    var ultima = '';
    var controlador = null;

    function quitarVacio() {
        var vacio = document.getElementById('vd-vacio');
        if (vacio) vacio.parentNode.removeChild(vacio);
    }

    function pintar(items, q) {
        quitarVacio();
        lista.innerHTML = '';
        items.forEach(function (it) {
            var li = document.createElement('li');
            var a = document.createElement('a');
            a.className = 'vd-item';
            a.href = it.url;
            var n = document.createElement('span');
            n.className = 'vd-item-nombre';
            n.textContent = it.nombre;
            var d = document.createElement('span');
            d.className = 'vd-item-detalle';
            d.textContent = it.detalle;
            a.appendChild(n);
            a.appendChild(d);
            li.appendChild(a);
            lista.appendChild(li);
        });
        if (!items.length && q) {
            contador.textContent = 'No encontramos medicamentos con «' + q + '». Probá con el principio activo o con menos letras.';
        } else {
            contador.textContent = items.length ? items.length + (items.length === 1 ? ' resultado' : ' resultados') : '';
        }
    }

    function buscar() {
        var q = input.value.trim();
        if (q === ultima) return;
        ultima = q;
        if (q.length < 2) {
            pintar([], '');
            return;
        }
        if (controlador && controlador.abort) controlador.abort();
        controlador = window.AbortController ? new AbortController() : null;
        fetch('/medicamentos/buscar?q=' + encodeURIComponent(q), controlador ? { signal: controlador.signal } : {})
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (items) {
                pintar(items, q);
                if (window.history && history.replaceState) {
                    history.replaceState(null, '', '/medicamentos/?q=' + encodeURIComponent(q));
                }
            })
            .catch(function (e) {
                if (e && e.name === 'AbortError') return;
                contador.textContent = 'No pudimos buscar en este momento. Volvé a intentarlo en unos minutos.';
            });
    }

    input.addEventListener('input', function () {
        clearTimeout(temporizador);
        temporizador = setTimeout(buscar, 250);
    });
    input.form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        clearTimeout(temporizador);
        buscar();
    });
    ultima = input.value.trim();
})();
