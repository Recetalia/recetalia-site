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

    // Los atajos "Buscar un medicamento" (#vd-q) llevan el foco al buscador.
    Array.prototype.forEach.call(document.querySelectorAll('a[href="#vd-q"]'), function (a) {
        a.addEventListener('click', function (e) {
            var q = document.getElementById('vd-q');
            if (!q) return;
            e.preventDefault();
            q.scrollIntoView({ block: 'center' });
            q.focus({ preventScroll: true });
        });
    });

    // ---- Búsqueda en vivo: sugerencias en un panel desplegable ---------------------------------
    // Enter / "Buscar" manda el formulario (?q=) y la lista completa sale renderizada desde el servidor.
    var input = document.getElementById('vd-q');
    var panel = document.getElementById('vd-panel');
    var lista = document.getElementById('vd-sugerencias');
    var verTodos = document.getElementById('vd-ver-todos');
    var estado = document.getElementById('vd-sug-estado');
    if (!input || !panel || !lista || !window.fetch) return;

    var MAX_SUG = 8;
    var temporizador = null;
    var ultima = input.value.trim();
    var controlador = null;

    function abrir(si) {
        panel.hidden = !si;
        input.setAttribute('aria-expanded', si ? 'true' : 'false');
    }
    function opciones() { return Array.prototype.slice.call(lista.querySelectorAll('.vd-item')); }

    function pintar(items, q) {
        lista.innerHTML = '';
        items.slice(0, MAX_SUG).forEach(function (it, i) {
            var li = document.createElement('li');
            li.setAttribute('role', 'presentation');
            var a = document.createElement('a');
            a.className = 'vd-item';
            a.href = it.url;
            a.id = 'vd-sug-' + i;
            a.setAttribute('role', 'option');
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
        if (!items.length) {
            var vacio = document.createElement('li');
            vacio.className = 'vd-sug-vacio';
            vacio.textContent = 'No encontramos medicamentos con «' + q + '». Probá con el principio activo o con menos letras.';
            lista.appendChild(vacio);
        }
        verTodos.hidden = items.length <= MAX_SUG;
        verTodos.href = '/medicamentos/?q=' + encodeURIComponent(q);
        verTodos.textContent = 'Ver los ' + items.length + ' resultados';
        estado.textContent = items.length ? items.length + (items.length === 1 ? ' resultado' : ' resultados') : 'Sin resultados';
        abrir(true);
    }

    function buscar() {
        var q = input.value.trim();
        if (q === ultima && !panel.hidden) return;
        ultima = q;
        if (q.length < 2) { abrir(false); lista.innerHTML = ''; return; }
        if (controlador && controlador.abort) controlador.abort();
        controlador = window.AbortController ? new AbortController() : null;
        fetch('/medicamentos/buscar?q=' + encodeURIComponent(q), controlador ? { signal: controlador.signal } : {})
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (items) { if (input.value.trim() === q) pintar(items, q); })
            .catch(function (e) {
                if (e && e.name === 'AbortError') return;
                lista.innerHTML = '<li class="vd-sug-vacio">No pudimos buscar en este momento. Volvé a intentarlo en unos minutos.</li>';
                verTodos.hidden = true;
                abrir(true);
            });
    }

    input.addEventListener('input', function () {
        clearTimeout(temporizador);
        temporizador = setTimeout(buscar, 220);
    });
    input.addEventListener('focus', function () { if (lista.children.length && input.value.trim().length >= 2) abrir(true); });
    // Teclado: flecha abajo entra al panel; arriba/abajo recorren; Escape cierra y vuelve al input.
    input.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown' && !panel.hidden) {
            var o = opciones();
            if (o.length) { e.preventDefault(); o[0].focus(); }
        } else if (e.key === 'Escape') {
            abrir(false);
        }
    });
    lista.addEventListener('keydown', function (e) {
        var o = opciones();
        var i = o.indexOf(document.activeElement);
        if (e.key === 'ArrowDown' && i < o.length - 1) { e.preventDefault(); o[i + 1].focus(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); if (i > 0) o[i - 1].focus(); else input.focus(); }
        else if (e.key === 'Escape') { e.preventDefault(); abrir(false); input.focus(); }
    });
    document.addEventListener('click', function (e) {
        if (!panel.hidden && !panel.contains(e.target) && e.target !== input) abrir(false);
    });
})();
