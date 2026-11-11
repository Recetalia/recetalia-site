// Verificador de interacciones: lista de medicamentos (localStorage, compartida con el botón
// "Agregar a mi lista" de las páginas de producto), buscadores y campos condicionales.
(function () {
    'use strict';
    var CLAVE = 'recetalia-mis-medicamentos';
    var MAX = 10;

    function leer() {
        try { var l = JSON.parse(localStorage.getItem(CLAVE) || '[]'); return Array.isArray(l) ? l : []; }
        catch (e) { return []; }
    }
    function guardar(l) {
        try { localStorage.setItem(CLAVE, JSON.stringify(l.slice(0, MAX))); } catch (e) { /* modo privado */ }
    }
    function idDeUrl(url) {
        var m = /-([0-9]+)$/.exec(url || '');
        return m ? m[1] : null;
    }
    function el(tag, clase, texto) {
        var e = document.createElement(tag);
        if (clase) e.className = clase;
        if (texto != null) e.textContent = texto;
        return e;
    }

    // ---- Botón "Agregar a mi lista" (páginas de producto) ----------------------------------
    var agregar = document.querySelector('[data-vr-agregar]');
    if (agregar) {
        var pintarBoton = function () {
            var esta = leer().some(function (x) { return x.id === agregar.getAttribute('data-id'); });
            agregar.textContent = esta ? 'En tu lista ✓ — Ver interacciones' : 'Agregar a mi lista para revisar interacciones';
        };
        agregar.addEventListener('click', function () {
            var l = leer();
            var id = agregar.getAttribute('data-id');
            if (l.some(function (x) { return x.id === id; })) {
                window.location.href = '/medicamentos/interacciones/';
                return;
            }
            if (l.length >= MAX) { agregar.textContent = 'Tu lista ya tiene ' + MAX + ' medicamentos'; return; }
            l.push({ id: id, nombre: agregar.getAttribute('data-nombre'), detalle: agregar.getAttribute('data-detalle') });
            guardar(l);
            pintarBoton();
        });
        pintarBoton();
    }

    var form = document.getElementById('vr-form');
    if (!form) return;

    // ---- Lista de medicamentos -------------------------------------------------------------
    var ul = document.getElementById('vr-lista');
    var vacia = document.getElementById('vr-lista-vacia');
    var inicial = [];
    try { inicial = JSON.parse(ul.getAttribute('data-inicial') || '[]'); } catch (e) { inicial = []; }
    if (inicial.length) guardar(inicial); // tras un error de validación, manda lo que vino del servidor

    function pintarLista() {
        var l = leer();
        ul.innerHTML = '';
        l.forEach(function (it, i) {
            var li = el('li', 'vr-item');
            var txt = el('span', 'vr-item-texto');
            txt.appendChild(el('span', 'vd-item-nombre', it.nombre));
            if (it.detalle) txt.appendChild(el('span', 'vd-item-detalle', it.detalle));
            var oculto = el('input');
            oculto.type = 'hidden'; oculto.name = 'productos[]'; oculto.value = it.id;
            var quitar = el('button', 'vr-quitar', 'Quitar');
            quitar.type = 'button';
            quitar.setAttribute('aria-label', 'Quitar ' + it.nombre);
            quitar.addEventListener('click', function () {
                var l2 = leer(); l2.splice(i, 1); guardar(l2); pintarLista();
            });
            li.appendChild(txt); li.appendChild(oculto); li.appendChild(quitar);
            ul.appendChild(li);
        });
        vacia.hidden = l.length > 0;
    }
    pintarLista();

    function autocompletar(input, lista, url, alElegir, minimo) {
        var t = null, ctrl = null;
        input.addEventListener('input', function () {
            clearTimeout(t);
            t = setTimeout(function () {
                var q = input.value.trim();
                lista.innerHTML = '';
                if (q.length < minimo) return;
                if (ctrl && ctrl.abort) ctrl.abort();
                ctrl = window.AbortController ? new AbortController() : null;
                fetch(url + encodeURIComponent(q), ctrl ? { signal: ctrl.signal } : {})
                    .then(function (r) { return r.ok ? r.json() : []; })
                    .then(function (items) {
                        lista.innerHTML = '';
                        items.slice(0, 8).forEach(function (it) {
                            var li = el('li');
                            var b = el('button', 'vd-item vr-sug');
                            b.type = 'button';
                            b.appendChild(el('span', 'vd-item-nombre', it.nombre));
                            if (it.detalle || it.codigo) b.appendChild(el('span', 'vd-item-detalle', it.detalle || it.codigo));
                            b.addEventListener('click', function () {
                                alElegir(it); input.value = ''; lista.innerHTML = ''; input.focus();
                            });
                            li.appendChild(b); lista.appendChild(li);
                        });
                    })
                    .catch(function () { /* abortado o sin red: sin sugerencias */ });
            }, 250);
        });
        input.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
    }

    autocompletar(document.getElementById('vr-buscar'), document.getElementById('vr-sugerencias'),
        '/medicamentos/buscar?q=', function (it) {
            var id = idDeUrl(it.url);
            var l = leer();
            if (!id || l.some(function (x) { return x.id === id; })) return;
            if (l.length >= MAX) return;
            l.push({ id: id, nombre: it.nombre, detalle: it.detalle });
            guardar(l); pintarLista();
        }, 2);

    // ---- Enfermedades (CIE-10) -------------------------------------------------------------
    var enfUl = document.getElementById('vr-enf-lista');
    var enf = [];
    try { enf = JSON.parse(enfUl.getAttribute('data-inicial') || '[]'); } catch (e) { enf = []; }
    function pintarEnf() {
        enfUl.innerHTML = '';
        enf.forEach(function (p, i) {
            var li = el('li', 'vr-item');
            li.appendChild(el('span', 'vr-item-texto', p.nombre));
            var h = el('input');
            h.type = 'hidden'; h.name = 'patologias[]'; h.value = p.codigo + '|' + p.nombre;
            var q = el('button', 'vr-quitar', 'Quitar');
            q.type = 'button';
            q.setAttribute('aria-label', 'Quitar ' + p.nombre);
            q.addEventListener('click', function () { enf.splice(i, 1); pintarEnf(); });
            li.appendChild(h); li.appendChild(q); enfUl.appendChild(li);
        });
    }
    pintarEnf();
    autocompletar(document.getElementById('vr-enf'), document.getElementById('vr-enf-sug'),
        '/medicamentos/interacciones/enfermedades?q=', function (it) {
            if (enf.length >= 10 || enf.some(function (x) { return x.codigo === it.codigo; })) return;
            enf.push({ codigo: it.codigo, nombre: it.nombre }); pintarEnf();
        }, 3);

    // ---- Embarazo / lactancia sólo con sexo femenino ---------------------------------------
    var fem = document.getElementById('vr-femenino');
    function pintarSexo() {
        var f = form.querySelector('input[name="sexo"]:checked');
        var es = f && f.value === 'F';
        fem.hidden = !es;
        if (!es) fem.querySelectorAll('input').forEach(function (c) { c.checked = false; });
    }
    form.querySelectorAll('input[name="sexo"]').forEach(function (r) { r.addEventListener('change', pintarSexo); });
    pintarSexo();

    form.addEventListener('submit', function (e) {
        if (leer().length < 2) {
            e.preventDefault();
            vacia.hidden = false;
            vacia.textContent = 'Agregá al menos dos medicamentos a tu lista.';
            document.getElementById('vr-buscar').focus();
        }
    });
})();
