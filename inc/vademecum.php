<?php
// Funciones puras del vademécum público (/medicamentos). Sin DB, red ni salida, para
// probarlas con tests/vademecum.test.php. Compatibles con PHP 7.4.
// Spec: doc/plans/2026-11-11-vademecum-publico-design.md (repo del workspace).
require_once __DIR__ . '/farmacias-habilitadas.php'; // fh_normalizar

// Secciones de la ficha técnica que se muestran, en orden, con rótulo corto.
const VD_SECCIONES = array(
    '4.1' => 'Indicaciones',
    '4.2' => 'Posología y forma de administración',
    '4.3' => 'Contraindicaciones',
    '4.4' => 'Advertencias y precauciones',
    '4.5' => 'Interacciones',
    '4.6' => 'Embarazo y lactancia',
    '4.8' => 'Reacciones adversas',
);

// Etiquetas que sobreviven al saneado (lo que trae la ficha de la AEMPS). Sin links ni imágenes.
const VD_ETIQUETAS = '<p><ul><ol><li><table><thead><tbody><tr><td><th><b><strong><i><em><u><sub><sup><br>';

function vd_slug($texto) {
    $s = preg_replace('/[^a-z0-9]+/', '-', fh_normalizar($texto));
    $s = trim($s, '-');
    if (strlen($s) > 80) {
        $s = rtrim(substr($s, 0, 80), '-');
    }
    return $s === '' ? 'medicamento' : $s;
}

function vd_url($ampId, $dsc) {
    return '/medicamentos/' . vd_slug($dsc) . '-' . $ampId;
}

// "<slug>-<id>" → ['slug', 'id']; el id es el último tramo y tiene que ser numérico.
function vd_parse_ruta($ruta) {
    if (!preg_match('/^(?:(.*)-)?([0-9]+)$/', (string)$ruta, $m)) {
        return null;
    }
    return array('slug' => $m[1], 'id' => $m[2]);
}

// Sólo se rotulan los códigos verificados en el código de Recetalia (Condvta.java): 11 verde, 12 naranja.
function vd_condvta($id) {
    if ($id === '11') return 'Receta verde';
    if ($id === '12') return 'Receta naranja';
    return null;
}

// De la respuesta de Consilio: sólo las secciones de VD_SECCIONES, en su orden, con rótulo y HTML re-saneado.
function vd_secciones($secciones) {
    $porNumero = array();
    foreach ((array)$secciones as $s) {
        if (isset($s['seccion']) && isset(VD_SECCIONES[$s['seccion']])) {
            $porNumero[$s['seccion']] = $s;
        }
    }
    $salida = array();
    foreach (VD_SECCIONES as $numero => $rotulo) {
        if (isset($porNumero[$numero])) {
            $salida[] = array(
                'seccion' => $numero,
                'rotulo' => $rotulo,
                'titulo' => (string)$porNumero[$numero]['titulo'],
                'html' => vd_sanear($porNumero[$numero]['html']),
            );
        }
    }
    return $salida;
}

// Consilio ya sanea; esto es la segunda barrera antes de imprimir HTML de terceros.
// Etiquetas de VD_ETIQUETAS y ningún atributo salvo colspan/rowspan numéricos.
function vd_sanear($html) {
    $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', (string)$html);
    $html = strip_tags($html, VD_ETIQUETAS);
    return preg_replace_callback('#<([a-z0-9]+)(\s[^>]*)?>#i', function ($m) {
        $attrs = '';
        if (isset($m[2]) && preg_match_all('#\b(colspan|rowspan)="([0-9]+)"#i', $m[2], $a, PREG_SET_ORDER)) {
            foreach ($a as $par) {
                $attrs .= ' ' . strtolower($par[1]) . '="' . $par[2] . '"';
            }
        }
        return '<' . strtolower($m[1]) . $attrs . '>';
    }, $html);
}

function vd_fecha($iso) {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})/', (string)$iso, $m)) {
        return null;
    }
    return $m[3] . '/' . $m[2] . '/' . $m[1];
}

// El DNMA guarda los nombres en minúsculas: capitalizar la primera letra.
function vd_nombre($dsc) {
    $dsc = trim((string)$dsc);
    return mb_strtoupper(mb_substr($dsc, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($dsc, 1, null, 'UTF-8');
}

function vd_descripcion($nombre, $generico, $laboratorio) {
    $d = $nombre;
    if ($generico) $d .= ' (' . $generico . ')';
    if ($laboratorio) $d .= ', de ' . $laboratorio;
    return $d . ': ficha técnica de referencia, indicaciones, posología, contraindicaciones e interacciones.';
}

function vd_e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
