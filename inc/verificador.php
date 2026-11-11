<?php
// Lógica pura del verificador de interacciones para pacientes: validación del formulario, armado
// del pedido a Consilio y traducción de su respuesta (técnica) a alertas para pacientes. Sin DB,
// red ni salida: tests/verificador.test.php. Spec: doc/plans/2026-11-11-verificador-pacientes-design.md.
require_once __DIR__ . '/vademecum.php';
require_once __DIR__ . '/verificador-mensajes.php';

const VR_MAX_PRODUCTOS = 10;
const VR_MAX_ALERGIAS = 10;
const VR_MAX_PATOLOGIAS = 10;
const VR_ORDEN_GRAVEDAD = array('importante' => 0, 'moderada' => 1, 'leve' => 2, 'incierta' => 3);
const VR_FUENTES = array('aemps' => 'AEMPS', 'ddinter' => 'DDInter', 'openfda' => 'openFDA (en inglés)',
    'medrt' => 'MED-RT', 'consilio' => 'Consilio', 'recetalia' => 'Recetalia', 'interpolar' => 'MED-RT');

// ['errores' => [campo => mensaje], 'datos' => [...]]
function vr_validar(array $post) {
    $e = array();
    $d = array();

    $ids = array();
    foreach ((array)(isset($post['productos']) ? $post['productos'] : array()) as $id) {
        $id = trim((string)$id);
        if (!preg_match('/^[0-9]{1,20}$/', $id)) {
            $e['productos'] = 'Hay un medicamento inválido en la lista.';
            break;
        }
        $ids[$id] = true;
    }
    $d['productos'] = array_map('strval', array_keys($ids));
    if (!isset($e['productos'])) {
        if (count($d['productos']) < 2) {
            $e['productos'] = 'Elegí al menos dos medicamentos.';
        } elseif (count($d['productos']) > VR_MAX_PRODUCTOS) {
            $e['productos'] = 'Podés revisar hasta ' . VR_MAX_PRODUCTOS . ' medicamentos a la vez.';
        }
    }

    $edad = isset($post['edad']) ? trim((string)$post['edad']) : '';
    if (!ctype_digit($edad) || (int)$edad > 120) {
        $e['edad'] = 'Indicá tu edad en años (0 a 120).';
    }
    $d['edad'] = ctype_digit($edad) ? (int)$edad : null;

    $sexo = isset($post['sexo']) ? (string)$post['sexo'] : '';
    if ($sexo !== 'F' && $sexo !== 'M') {
        $e['sexo'] = 'Indicá tu sexo.';
    }
    $d['sexo'] = $sexo;
    $d['embarazo'] = $sexo === 'F' && !empty($post['embarazo']);
    $d['lactancia'] = $sexo === 'F' && !empty($post['lactancia']);

    // Alergias: del buscador llegan como "nombre canónico|nombre para mostrar" (alergias[]); se guarda y se
    // manda a Consilio el canónico. Un texto suelto (sin buscador) se acepta igual.
    $alergias = array();
    $crudas = isset($post['alergias']) ? $post['alergias'] : array();
    $crudas = is_array($crudas) ? $crudas : preg_split('/[\r\n,;]+/', (string)$crudas);
    foreach ($crudas as $a) {
        $partes = explode('|', (string)$a, 2);
        $a = trim(preg_replace('/\s+/', ' ', $partes[0]));
        if ($a !== '') {
            $alergias[] = mb_substr($a, 0, 100, 'UTF-8');
        }
    }
    $d['alergias'] = array_slice(array_values(array_unique($alergias)), 0, VR_MAX_ALERGIAS);

    $d['patologias'] = array();
    foreach (array_slice((array)(isset($post['patologias']) ? $post['patologias'] : array()), 0, VR_MAX_PATOLOGIAS) as $p) {
        $partes = explode('|', (string)$p, 2);
        if (!preg_match('/^[A-Z][0-9]{2}(\.[0-9A-Z]{1,4})?$/', $partes[0])) {
            $e['patologias'] = 'Hay una enfermedad inválida en la lista.';
            break;
        }
        $d['patologias'][] = array('codigo' => $partes[0], 'nombre' => isset($partes[1]) ? mb_substr(trim($partes[1]), 0, 200, 'UTF-8') : $partes[0]);
    }

    $email = strtolower(trim(isset($post['email']) ? (string)$post['email'] : ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 200) {
        $e['email'] = 'Escribí un email válido: ahí te mandamos el resultado.';
    }
    $d['email'] = $email;

    if (empty($post['consentimiento'])) {
        $e['consentimiento'] = 'Para continuar tenés que aceptar el uso de tus datos.';
    }
    $d['novedades'] = !empty($post['novedades']);

    return array('errores' => $e, 'datos' => $d);
}

// $productos: [{id, nombre, sustancias[]}] ya resueltos contra el DNMA.
function vr_productos_consilio(array $productos) {
    $out = array();
    foreach ($productos as $p) {
        if ($p['sustancias']) {
            $out[] = array('id' => (string)$p['id'], 'substances' => array_values($p['sustancias']));
        }
    }
    return $out;
}

function vr_drogas(array $productos) {
    $out = array();
    foreach ($productos as $p) {
        foreach ($p['sustancias'] as $s) {
            if (!in_array($s, $out, true)) {
                $out[] = $s;
            }
        }
    }
    return $out;
}

function vr_gravedad($severity, $uncertain = false) {
    if ($uncertain) return 'incierta';
    $m = array('major' => 'importante', 'contraindication' => 'importante', 'moderate' => 'moderada', 'minor' => 'leve');
    return isset($m[$severity]) ? $m[$severity] : 'incierta';
}

function vr_fuente($source) {
    return isset(VR_FUENTES[$source]) ? VR_FUENTES[$source] : (string)$source;
}

function vr_mensaje($tipo, $gravedad) {
    if (!isset(VR_MENSAJES[$tipo][$gravedad])) {
        return null;
    }
    list($titulo, $texto) = VR_MENSAJES[$tipo][$gravedad];
    return array('titulo' => $titulo, 'texto' => $texto . ' ' . VR_CIERRE);
}

function vr_alerta($tipo, $gravedad, array $productos, array $detalle) {
    return array('tipo' => $tipo, 'gravedad' => $gravedad, 'productos' => $productos,
        'detalle' => $detalle, 'mensaje' => vr_mensaje($tipo, $gravedad));
}

// Respuestas de Consilio (/interactions y /contraindications, decodificadas) → alertas para pacientes,
// ordenadas por gravedad. Un par de PRODUCTOS es una alerta (con un renglón por par de sustancias),
// como en la app de Médicos; las sustancias de un mismo producto combinado no se alertan entre sí.
function vr_alertas(array $productos, array $inter, array $contra) {
    $porSustancia = array();
    $nombre = array();
    foreach ($productos as $i => $p) {
        $nombre[(string)$p['id']] = $p['nombre'];
        foreach ($p['sustancias'] as $s) {
            $porSustancia[mb_strtolower($s, 'UTF-8')][] = $i;
        }
    }
    $prodDe = function ($sustancia) use ($porSustancia) {
        $k = mb_strtolower((string)$sustancia, 'UTF-8');
        return isset($porSustancia[$k]) ? array_values(array_unique($porSustancia[$k])) : array();
    };
    $nombres = function (array $idx) use ($productos) {
        return array_map(function ($i) use ($productos) { return $productos[$i]['nombre']; }, $idx);
    };

    $alertas = array();

    // Interacciones, agrupadas por par de productos.
    $grupos = array();
    foreach (isset($inter['interactions']) ? $inter['interactions'] : array() as $it) {
        $g = vr_gravedad(isset($it['severity']) ? $it['severity'] : '', !empty($it['uncertain']));
        $texto = $it['drug_a'] . ' + ' . $it['drug_b'] . ': ';
        $partes = array_filter(array(isset($it['description']) ? $it['description'] : null, isset($it['management']) ? $it['management'] : null));
        $texto .= $partes ? implode(' ', $partes) : 'la fuente registra la interacción sin descripción.';
        $renglon = array('texto' => $texto, 'fuente' => vr_fuente(isset($it['source']) ? $it['source'] : ''), 'gravedad' => $g);
        foreach ($prodDe($it['drug_a']) as $a) {
            foreach ($prodDe($it['drug_b']) as $b) {
                if ($a === $b) continue;
                $par = array(min($a, $b), max($a, $b));
                $k = implode('|', $par);
                if (!isset($grupos[$k])) {
                    $grupos[$k] = array('par' => $par, 'gravedad' => $g, 'detalle' => array());
                }
                if (VR_ORDEN_GRAVEDAD[$g] < VR_ORDEN_GRAVEDAD[$grupos[$k]['gravedad']]) {
                    $grupos[$k]['gravedad'] = $g;
                }
                if (!in_array($renglon, $grupos[$k]['detalle'], true)) {
                    $grupos[$k]['detalle'][] = $renglon;
                }
            }
        }
    }
    foreach ($grupos as $gr) {
        usort($gr['detalle'], function ($x, $y) { return VR_ORDEN_GRAVEDAD[$x['gravedad']] - VR_ORDEN_GRAVEDAD[$y['gravedad']]; });
        $alertas[] = vr_alerta('interaccion', $gr['gravedad'], $nombres($gr['par']), $gr['detalle']);
    }

    // Duplicidad: Consilio devuelve los ids de producto que le mandamos.
    foreach (isset($inter['duplicities']) ? $inter['duplicities'] : array() as $d) {
        $prods = array();
        foreach ((array)$d['products'] as $id) {
            if (isset($nombre[(string)$id])) $prods[] = $nombre[(string)$id];
        }
        $texto = (isset($d['layer']) && $d['layer'] === 'class')
            ? 'Misma clase terapéutica: ' . (isset($d['class_desc']) ? $d['class_desc'] : '') . ' (' . implode(', ', (array)$d['substances']) . ').'
            : 'Misma sustancia: ' . implode(', ', (array)$d['substances']) . '.';
        $alertas[] = vr_alerta('duplicidad', vr_gravedad(isset($d['severity']) ? $d['severity'] : ''), $prods,
            array(array('texto' => $texto, 'fuente' => vr_fuente(isset($d['source']) ? $d['source'] : ''))));
    }

    // Perfil: contraindicaciones (embarazo / lactancia / enfermedad), precauciones, alergias, mayores.
    $porProducto = function ($drugInput, $tipo, $gravedad, $texto, $fuente) use (&$alertas, $prodDe, $nombres) {
        $idx = $prodDe($drugInput);
        if (!$idx) return;
        $alertas[] = vr_alerta($tipo, $gravedad, $nombres($idx), array(array('texto' => $texto, 'fuente' => $fuente)));
    };
    foreach (isset($contra['contraindications']) ? $contra['contraindications'] : array() as $c) {
        $cond = (string)(isset($c['condition']) ? $c['condition'] : '');
        $tipo = preg_match('/pregnan|embaraz/i', $cond) ? 'embarazo'
            : (preg_match('/lactat|breast|lactanc/i', $cond) ? 'lactancia' : 'enfermedad');
        $texto = 'Contraindicado con: ' . $cond . (empty($c['detail']) ? '.' : '. ' . $c['detail']);
        $porProducto($c['drug_input'], $tipo, 'importante', $texto, vr_fuente(isset($c['source']) ? $c['source'] : ''));
    }
    foreach (isset($contra['precautions']) ? $contra['precautions'] : array() as $c) {
        $cond = (string)(isset($c['condition']) ? $c['condition'] : '');
        $tipo = preg_match('/pregnan|embaraz/i', $cond) ? 'embarazo'
            : (preg_match('/lactat|breast|lactanc/i', $cond) ? 'lactancia' : 'precaucion');
        $texto = 'Precaución con: ' . $cond . (empty($c['detail']) ? '.' : '. ' . $c['detail']);
        $porProducto($c['drug_input'], $tipo, 'moderada', $texto, vr_fuente(isset($c['source']) ? $c['source'] : ''));
    }
    foreach (isset($contra['allergies']) ? $contra['allergies'] : array() as $al) {
        $g = (isset($al['match']) && $al['match'] === 'exact') ? 'importante' : 'incierta';
        $texto = 'Alergia declarada: «' . (isset($al['declared_as']) ? $al['declared_as'] : '') . '».'
            . (empty($al['note']) ? '' : ' ' . $al['note']);
        if (!empty($al['drug_input'])) {
            $porProducto($al['drug_input'], 'alergia', $g, $texto, 'Consilio');
        } else {
            $alertas[] = vr_alerta('alergia', 'incierta', array(), array(array('texto' => $texto, 'fuente' => 'Consilio')));
        }
    }
    foreach (isset($contra['geriatric']) ? $contra['geriatric'] : array() as $ge) {
        $texto = (empty($ge['condicionada']) ? '' : 'Verificar si corresponde: ')
            . (isset($ge['situacion']) ? $ge['situacion'] : '') . '. Recomendación: ' . (isset($ge['recomendacion']) ? $ge['recomendacion'] : '');
        $porProducto($ge['drug_input'], 'mayores', 'moderada', $texto, vr_fuente(isset($ge['source']) ? $ge['source'] : 'aemps'));
    }

    // Orden estable por gravedad (usort no es estable en PHP 7.4).
    foreach ($alertas as $i => &$a) { $a['_i'] = $i; }
    unset($a);
    usort($alertas, function ($x, $y) {
        return (VR_ORDEN_GRAVEDAD[$x['gravedad']] - VR_ORDEN_GRAVEDAD[$y['gravedad']]) ?: ($x['_i'] - $y['_i']);
    });
    foreach ($alertas as &$a) { unset($a['_i']); }
    unset($a);
    return $alertas;
}

function vr_resumen(array $alertas) {
    $r = array('total' => count($alertas), 'importantes' => 0, 'por_tipo' => array());
    foreach ($alertas as $a) {
        if ($a['gravedad'] === 'importante') $r['importantes']++;
        $r['por_tipo'][$a['tipo']] = (isset($r['por_tipo'][$a['tipo']]) ? $r['por_tipo'][$a['tipo']] : 0) + 1;
    }
    return $r;
}
