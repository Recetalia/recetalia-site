<?php
// Pruebas de las funciones puras de farmacias-habilitadas. Sin framework:
//   php tests/farmacias-habilitadas.test.php
// Sale con 1 si algo falla. Correr también en php:7.4-cli (ver plan).
require_once __DIR__ . '/../inc/farmacias-habilitadas.php';

$fallos = 0;
function ok($cond, $msg) {
    global $fallos;
    if ($cond) { echo "  ok    $msg\n"; } else { $fallos++; echo "  FALLO $msg\n"; }
}

echo "fh_normalizar\n";
ok(fh_normalizar('Farmacia Río  Negro') === 'farmacia rio negro', 'minúsculas, sin tildes, espacios colapsados');
ok(fh_normalizar('Ñandú Ünico') === 'nandu unico', 'ñ y diéresis');
ok(fh_normalizar(null) === '', 'null → vacío');
ok(fh_normalizar("Ri\xCC\x81o Negro") === 'rio negro', 'tilde combinante (NFD) también se quita');
ok(fh_normalizar("Farmacia\xC2\xA0Central") === 'farmacia central', 'NBSP → espacio');
if (class_exists('Normalizer')) {
    ok(fh_normalizar('Ançã São') === 'anca sao', 'ç/ã con intl (Normalizer)');
}

echo "fh_telefono\n";
ok(fh_telefono('{"national":"2924 1234","international":"+59829241234"}') === array('national' => '2924 1234', 'international' => '+59829241234'), 'json completo');
ok(fh_telefono('{"national":"2924 1234"}') === array('national' => '2924 1234', 'international' => '2924 1234'), 'sin international usa national');
ok(fh_telefono(null) === null, 'null');
ok(fh_telefono('{"number":null}') === null, 'sin national');
ok(fh_telefono('no es json') === null, 'basura');

echo "fh_agrupar\n";
$filas = array(
    array('name' => 'Farmacia Rivera', 'addressStreet' => 'Sarandí', 'addressNumber' => '100', 'phone' => null, 'regionName' => 'Rivera', 'localityName' => 'Rivera'),
    array('name' => 'Farmacia Aguada', 'addressStreet' => ' Av. Gral. Rondeau ', 'addressNumber' => '1795', 'phone' => '{"national":"2924 1234","international":"+59829241234"}', 'regionName' => 'Montevideo', 'localityName' => 'Montevideo'),
    array('name' => 'Farmacia Fray Bentos', 'addressStreet' => '18 de Julio', 'addressNumber' => '', 'phone' => null, 'regionName' => 'Río Negro', 'localityName' => 'Fray Bentos'),
);
$d = fh_agrupar($filas);
ok($d['total'] === 3, 'total');
ok(array_keys($d['regiones']) === array('Montevideo', 'Río Negro', 'Rivera'), 'regiones ordenadas sin tildes (Río Negro antes que Rivera)');
ok($d['regiones']['Montevideo']['cantidad'] === 1, 'cantidad por región');
$f = $d['regiones']['Montevideo']['farmacias'][0];
ok($f['direccion'] === 'Av. Gral. Rondeau 1795', 'dirección recortada');
ok($f['busqueda'] === 'farmacia aguada montevideo av. gral. rondeau 1795', 'texto de búsqueda');
ok($f['telefono']['international'] === '+59829241234', 'teléfono');
ok($d['regiones']['Río Negro']['farmacias'][0]['direccion'] === '18 de Julio', 'sin número no deja espacio colgando');
ok($d['regiones']['Rivera']['farmacias'][0]['telefono'] === null, 'sin teléfono');
ok(fh_agrupar(array()) === array('total' => 0, 'regiones' => array()), 'sin filas');

echo "fh_region_inicial\n";
ok(fh_region_inicial($d['regiones']) === 'Montevideo', 'Montevideo si existe');
ok(fh_region_inicial(array('Salto' => array())) === 'Salto', 'si no, la primera');
ok(fh_region_inicial(array()) === '', 'vacío');

echo "fh_plural\n";
ok(fh_plural(1, 'farmacia', 'farmacias') === '1 farmacia', 'singular');
ok(fh_plural(151, 'farmacia', 'farmacias') === '151 farmacias', 'plural');

echo $fallos ? "\n$fallos FALLOS\n" : "\nTodo OK\n";
exit($fallos ? 1 : 0);
