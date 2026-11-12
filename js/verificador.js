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

    // ---- Resultado: imprimir o guardar como PDF -------------------------------------------
    var imprimir = document.querySelector('[data-vr-imprimir]');
    if (imprimir && window.print) {
        imprimir.hidden = false;
        imprimir.addEventListener('click', function () { window.print(); });
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
                            b.appendChild(el('span', 'vd-item-nombre', it.mostrar || it.nombre));
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

    // ---- Etiquetas elegidas de un buscador (enfermedades CIE-10, alergias) -------------------
    // clave: lo que identifica (y se manda); texto: lo que se muestra; valor: el hidden que viaja.
    function etiquetas(idLista, campo, clave, texto, valor) {
        var ul2 = document.getElementById(idLista);
        var items = [];
        try { items = JSON.parse(ul2.getAttribute('data-inicial') || '[]'); } catch (e) { items = []; }
        function pintar() {
            ul2.innerHTML = '';
            items.forEach(function (p, i) {
                var li = el('li', 'vr-item');
                li.appendChild(el('span', 'vr-item-texto', texto(p)));
                var h = el('input');
                h.type = 'hidden'; h.name = campo; h.value = valor(p);
                var q = el('button', 'vr-quitar', 'Quitar');
                q.type = 'button';
                q.setAttribute('aria-label', 'Quitar ' + texto(p));
                q.addEventListener('click', function () { items.splice(i, 1); pintar(); });
                li.appendChild(h); li.appendChild(q); ul2.appendChild(li);
            });
        }
        pintar();
        return function (it) {
            if (items.length >= 10 || items.some(function (x) { return clave(x) === clave(it); })) return;
            items.push(it); pintar();
        };
    }
    var agregarEnf = etiquetas('vr-enf-lista', 'patologias[]',
        function (p) { return p.codigo; }, function (p) { return p.nombre; }, function (p) { return p.codigo + '|' + p.nombre; });
    autocompletar(document.getElementById('vr-enf'), document.getElementById('vr-enf-sug'),
        '/medicamentos/interacciones/enfermedades?q=', function (it) { agregarEnf({ codigo: it.codigo, nombre: it.nombre }); }, 3);
    var agregarAl = etiquetas('vr-alergias-lista', 'alergias[]',
        function (p) { return p.nombre; }, function (p) { return p.mostrar; }, function (p) { return p.nombre + '|' + p.mostrar; });
    autocompletar(document.getElementById('vr-alergias'), document.getElementById('vr-alergias-sug'),
        '/medicamentos/interacciones/farmacos?q=', function (it) { agregarAl({ nombre: it.nombre, mostrar: it.mostrar }); }, 3);

    // ---- Embarazo / lactancia sólo con sexo femenino ---------------------------------------
    var fem = document.getElementById('vr-femenino');
    var femAyuda = document.getElementById('vr-femenino-ayuda');
    function pintarSexo() {
        var f = form.querySelector('input[name="sexo"]:checked');
        var es = !!(f && f.value === 'F');
        fem.classList.toggle('vr-deshabilitado', !es);
        femAyuda.hidden = es;
        fem.querySelectorAll('input').forEach(function (c) { c.disabled = !es; if (!es) c.checked = false; });
    }
    form.querySelectorAll('input[name="sexo"]').forEach(function (r) { r.addEventListener('change', pintarSexo); });
    pintarSexo();

    // ---- Indicador de progreso (stepper): 1 hecho con 2+ medicamentos; 2 hecho con edad, sexo y email.
    var etapas = document.querySelectorAll('#vr-stepper .vr-etapa');
    function actualizarPasos() {
        if (etapas.length < 3) return;
        var uno = leer().length >= 2;
        var edad = form.querySelector('[name="edad"]').value.trim() !== '';
        var sexo = !!form.querySelector('input[name="sexo"]:checked');
        var mail = form.querySelector('[name="email"]');
        var dos = uno && edad && sexo && mail.value.trim() !== '' && (!mail.checkValidity || mail.checkValidity());
        var hechos = [uno, dos, false];
        var actual = !uno ? 0 : (!dos ? 1 : 2);
        Array.prototype.forEach.call(etapas, function (li, i) {
            li.classList.toggle('is-hecho', hechos[i]);
            li.classList.toggle('is-actual', i === actual);
            if (i === actual) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
        });
    }
    if (window.MutationObserver) new MutationObserver(actualizarPasos).observe(ul, { childList: true });
    form.addEventListener('input', actualizarPasos);
    form.addEventListener('change', actualizarPasos);
    actualizarPasos();

    form.addEventListener('submit', function (e) {
        if (leer().length < 2) {
            e.preventDefault();
            vacia.hidden = false;
            vacia.textContent = 'Agregá al menos dos medicamentos a tu lista.';
            document.getElementById('vr-buscar').focus();
        }
    });
})();
