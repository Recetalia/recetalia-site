<?php
// Pruebas de las funciones puras del vademécum. Sin framework:
//   php tests/vademecum.test.php
// Sale con 1 si algo falla. Correr también en php:7.4-cli (la imagen es 7.4).
require_once __DIR__ . '/../inc/vademecum.php';
require_once __DIR__ . '/../inc/vademecum-consilio.php';

$fallos = 0;
function ok($cond, $msg) {
    global $fallos;
    if ($cond) { echo "  ok    $msg\n"; } else { $fallos++; echo "  FALLO $msg\n"; }
}

echo "vd_slug\n";
ok(vd_slug('perifar 400 comprimido recubierto (Spefar)') === 'perifar-400-comprimido-recubierto-spefar', 'espacios y paréntesis');
ok(vd_slug('Ácido Clavulánico + Amoxicilina 875/125 mg') === 'acido-clavulanico-amoxicilina-875-125-mg', 'tildes, + y /');
ok(vd_slug('Ñandú') === 'nandu', 'ñ');
ok(strlen(vd_slug(str_repeat('abcde ', 40))) <= 80, 'máximo 80 caracteres');
ok(substr(vd_slug(str_repeat('abcde ', 40)), -1) !== '-', 'no termina en guion al cortar');
ok(vd_slug('') === 'medicamento', 'vacío → medicamento');

echo "vd_url / vd_parse_ruta\n";
ok(vd_url('100001000179109', 'perifar 400 (Spefar)') === '/medicamentos/perifar-400-spefar-100001000179109', 'url canónica');
ok(vd_parse_ruta('perifar-400-spefar-100001000179109') === array('slug' => 'perifar-400-spefar', 'id' => '100001000179109'), 'ruta válida');
ok(vd_parse_ruta('100001000179109') === array('slug' => '', 'id' => '100001000179109'), 'sólo id');
ok(vd_parse_ruta('perifar-400') === array('slug' => 'perifar', 'id' => '400'), 'el último tramo numérico es el id');
ok(vd_parse_ruta('perifar') === null, 'sin id');
ok(vd_parse_ruta('perifar-12a') === null, 'id no numérico');
ok(vd_parse_ruta('') === null, 'vacío');

echo "vd_condvta\n";
ok(vd_condvta('11') === 'Receta verde', '11');
ok(vd_condvta('12') === 'Receta naranja', '12');
ok(vd_condvta('8') === null && vd_condvta(null) === null, 'otros → null');

echo "vd_secciones\n";
$sec = vd_secciones(array(
    array('seccion' => '4.8', 'titulo' => 'Reacciones adversas', 'html' => '<p>c</p>'),
    array('seccion' => '4.1', 'titulo' => 'Indicaciones terapéuticas', 'html' => '<p>a</p>'),
    array('seccion' => '4.7', 'titulo' => 'Efectos sobre la capacidad', 'html' => '<p>x</p>'),
    array('seccion' => '4.3', 'titulo' => 'Contraindicaciones', 'html' => '<p>b</p>'),
));
ok(array_map(function ($s) { return $s['seccion']; }, $sec) === array('4.1', '4.3', '4.8'), 'ordena y descarta 4.7');
ok($sec[0]['rotulo'] === 'Indicaciones' && $sec[2]['rotulo'] === 'Reacciones adversas', 'rótulos cortos');
ok($sec[0]['html'] === '<p>a</p>', 'html saneado conservado');
ok(vd_secciones(null) === array(), 'null → vacío');

echo "vd_sanear\n";
ok(vd_sanear('<p style="color:red" onclick="x()">Hola <b>mundo</b></p>') === '<p>Hola <b>mundo</b></p>', 'quita atributos');
ok(vd_sanear('<p>a</p><script>alert(1)</script><style>p{}</style>') === '<p>a</p>', 'quita script y style con contenido');
ok(vd_sanear('<p>x<img src="y.png" alt="z"></p>') === '<p>x</p>', 'quita img');
ok(vd_sanear('<table><tr><td colspan="2" class="c">t</td></tr></table>') === '<table><tr><td colspan="2">t</td></tr></table>', 'tabla con colspan');
ok(vd_sanear('<td colspan="2 onmouseover=x">t</td>') === '<td>t</td>', 'colspan no numérico se descarta');
ok(vd_sanear('<a href="javascript:x">l</a>') === 'l', 'sin links');

echo "vd_fecha / vd_nombre / vd_descripcion\n";
ok(vd_fecha('2025-08-27') === '27/08/2025', 'iso → dd/mm/aaaa');
ok(vd_fecha(null) === null && vd_fecha('basura') === null, 'inválida → null');
ok(vd_nombre('perifar 400 comprimido') === 'Perifar 400 comprimido', 'capitaliza la primera');
ok(vd_nombre('ácido fólico') === 'Ácido fólico', 'capitaliza multibyte');
ok(vd_descripcion('Perifar 400', 'ibuprofeno 400 mg comprimido', 'Spefar') === 'Perifar 400 (ibuprofeno 400 mg comprimido), de Spefar: ficha técnica de referencia, indicaciones, posología, contraindicaciones e interacciones.', 'meta description');

echo "vd_clave_cache\n";
ok(vd_clave_cache(array('Paracetamol', 'Ibuprofeno')) === vd_clave_cache(array('ibuprofeno', 'paracetamol')), 'independiente del orden y mayúsculas');
ok(vd_clave_cache(array('a')) !== vd_clave_cache(array('b')), 'distintas composiciones, distinta clave');

echo "vd_cache_leer / vd_cache_escribir\n";
$dir = sys_get_temp_dir() . '/vd-test-' . getmypid();
vd_cache_escribir($dir, 'k', array('disponible' => true));
$c = vd_cache_leer($dir, 'k', 3600);
ok($c['data'] === array('disponible' => true) && $c['vencida'] === false, 'recién escrita, vigente');
touch($dir . '/k.json', time() - 7200);
$c = vd_cache_leer($dir, 'k', 3600);
ok($c['data'] === array('disponible' => true) && $c['vencida'] === true, 'vieja, vencida pero legible');
ok(vd_cache_leer($dir, 'nada', 3600) === null, 'inexistente → null');
array_map('unlink', glob($dir . '/*'));
rmdir($dir);

echo $fallos ? "\n$fallos fallo(s)\n" : "\ntodo ok\n";
exit($fallos ? 1 : 0);
