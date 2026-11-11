<?php
// Pruebas de la lógica pura del verificador de interacciones para pacientes. Sin framework:
//   php tests/verificador.test.php
require_once __DIR__ . '/../inc/verificador.php';

$fallos = 0;
function ok($cond, $msg) {
    global $fallos;
    if ($cond) { echo "  ok    $msg\n"; } else { $fallos++; echo "  FALLO $msg\n"; }
}

function post_base(array $extra = array()) {
    return array_merge(array(
        'productos' => array('100001000179109', '200'),
        'edad' => '45', 'sexo' => 'F', 'embarazo' => '1', 'lactancia' => '',
        'alergias' => "penicilina\n\n  ibuprofeno  ",
        'patologias' => array('N18.5|Enfermedad renal crónica, estadio 5'),
        'email' => ' Ana@Example.com ', 'consentimiento' => '1', 'novedades' => '',
    ), $extra);
}

echo "vr_validar\n";
$v = vr_validar(post_base());
ok($v['errores'] === array(), 'formulario válido sin errores');
ok($v['datos']['email'] === 'ana@example.com', 'email normalizado');
ok($v['datos']['edad'] === 45 && $v['datos']['sexo'] === 'F', 'edad y sexo');
ok($v['datos']['embarazo'] === true && $v['datos']['lactancia'] === false, 'embarazo / lactancia');
ok($v['datos']['alergias'] === array('penicilina', 'ibuprofeno'), 'alergias: líneas limpias, sin vacías');
ok($v['datos']['patologias'] === array(array('codigo' => 'N18.5', 'nombre' => 'Enfermedad renal crónica, estadio 5')), 'patologías código|nombre');
ok($v['datos']['novedades'] === false, 'novedades opcional');
$v = vr_validar(post_base(array('sexo' => 'M', 'embarazo' => '1')));
ok($v['datos']['embarazo'] === false, 'embarazo se ignora si sexo M');
ok(isset(vr_validar(post_base(array('productos' => array('1'))))['errores']['productos']), 'menos de 2 productos');
ok(isset(vr_validar(post_base(array('productos' => array('1', '1'))))['errores']['productos']), 'productos repetidos cuentan una vez');
ok(isset(vr_validar(post_base(array('productos' => array('1', 'x;drop'))))['errores']['productos']), 'id no numérico');
ok(isset(vr_validar(post_base(array('productos' => range(1, 11))))['errores']['productos']), 'más de 10 productos');
ok(isset(vr_validar(post_base(array('edad' => '130')))['errores']['edad']), 'edad fuera de rango');
ok(isset(vr_validar(post_base(array('edad' => 'abc')))['errores']['edad']), 'edad no numérica');
ok(isset(vr_validar(post_base(array('sexo' => 'X')))['errores']['sexo']), 'sexo inválido');
ok(isset(vr_validar(post_base(array('email' => 'no-es-email')))['errores']['email']), 'email inválido');
ok(isset(vr_validar(post_base(array('consentimiento' => '')))['errores']['consentimiento']), 'sin consentimiento');
ok(isset(vr_validar(post_base(array('patologias' => array('<script>|x'))))['errores']['patologias']), 'código CIE-10 inválido');

// Productos ya resueltos contra el DNMA: id, nombre, sustancias.
$productos = array(
    array('id' => 'P1', 'nombre' => 'Perifar 400', 'sustancias' => array('ibuprofeno')),
    array('id' => 'P2', 'nombre' => 'Coumadin 5 mg', 'sustancias' => array('warfarina')),
    array('id' => 'P3', 'nombre' => 'Dolostop Plus', 'sustancias' => array('paracetamol', 'ibuprofeno')),
);

echo "vr_productos_consilio\n";
ok(vr_productos_consilio($productos) === array(
    array('id' => 'P1', 'substances' => array('ibuprofeno')),
    array('id' => 'P2', 'substances' => array('warfarina')),
    array('id' => 'P3', 'substances' => array('paracetamol', 'ibuprofeno')),
), 'forma de products');
ok(vr_drogas($productos) === array('ibuprofeno', 'warfarina', 'paracetamol'), 'sustancias sin repetir');

echo "vr_alertas: interacciones\n";
$inter = array(
    'interactions' => array(
        array('drug_a' => 'ibuprofeno', 'drug_b' => 'warfarina', 'severity' => 'major', 'source' => 'aemps',
              'description' => 'Aumento del riesgo de hemorragias.', 'management' => 'Asociación desaconsejada.', 'uncertain' => false),
        array('drug_a' => 'Warfarina', 'drug_b' => 'paracetamol', 'severity' => 'moderate', 'source' => 'ddinter',
              'description' => null, 'management' => null, 'uncertain' => false),
        array('drug_a' => 'ibuprofeno', 'drug_b' => 'paracetamol', 'severity' => 'unknown', 'source' => 'ddinter',
              'description' => null, 'management' => null, 'uncertain' => false),
    ),
    'duplicities' => array(
        array('layer' => 'substance', 'products' => array('P1', 'P3'), 'substances' => array('ibuprofeno'), 'severity' => 'major', 'source' => 'consilio'),
    ),
);
$a = vr_alertas($productos, $inter, array());
$inters = array_values(array_filter($a, function ($x) { return $x['tipo'] === 'interaccion'; }));
$pares = array_map(function ($x) { return implode(' + ', $x['productos']); }, $inters);
ok(in_array('Perifar 400 + Coumadin 5 mg', $pares, true), 'par P1-P2 por ibuprofeno-warfarina');
ok(in_array('Coumadin 5 mg + Dolostop Plus', $pares, true), 'par P2-P3 agrupa ibuprofeno-warfarina y warfarina-paracetamol');
ok(count($inters) === 3, 'tres pares de productos: P1-P2, P2-P3 y P1-P3 (ibuprofeno de Perifar con paracetamol de Dolostop)');
$p13 = $inters[array_search('Perifar 400 + Dolostop Plus', $pares, true)];
ok(count($p13['detalle']) === 1 && $p13['gravedad'] === 'incierta', 'P1-P3 sólo por ibuprofeno-paracetamol (el par interno de Dolostop no se repite), gravedad desconocida → incierta');
$p23 = $inters[array_search('Coumadin 5 mg + Dolostop Plus', $pares, true)];
ok($p23['gravedad'] === 'importante', 'el grupo toma la gravedad más alta');
ok(count($p23['detalle']) === 2, 'detalle con un renglón por par de sustancias');
ok($p23['detalle'][0]['fuente'] === 'AEMPS' && strpos($p23['detalle'][0]['texto'], 'hemorragias') !== false, 'detalle técnico textual con fuente');
ok(strpos($p23['detalle'][1]['texto'], 'sin descripción') !== false && $p23['detalle'][1]['fuente'] === 'DDInter', 'DDInter sin texto lo dice');
ok($p23['mensaje'] === vr_mensaje('interaccion', 'importante'), 'mensaje para pacientes de la matriz');

echo "vr_alertas: duplicidad\n";
$dups = array_values(array_filter($a, function ($x) { return $x['tipo'] === 'duplicidad'; }));
ok(count($dups) === 1 && $dups[0]['productos'] === array('Perifar 400', 'Dolostop Plus'), 'duplicidad con nombres de producto');
ok($dups[0]['mensaje'] === vr_mensaje('duplicidad', 'importante'), 'mensaje de duplicidad');

echo "vr_alertas: perfil\n";
$contra = array(
    'contraindications' => array(
        array('drug_input' => 'warfarina', 'condition' => 'Pregnancy', 'source' => 'medrt', 'detail' => null),
        array('drug_input' => 'ibuprofeno', 'condition' => 'Kidney Failure, Chronic', 'source' => 'medrt', 'detail' => null),
        array('drug_input' => 'ibuprofeno', 'condition' => 'Lactation', 'source' => 'medrt', 'detail' => null),
    ),
    'precautions' => array(array('drug_input' => 'paracetamol', 'condition' => 'Liver Diseases', 'source' => 'medrt', 'detail' => null)),
    'allergies' => array(array('drug_input' => 'ibuprofeno', 'declared_as' => 'ibuprofeno', 'match' => 'exact', 'severity' => 'contraindication')),
    'geriatric' => array(array('drug_input' => 'warfarina', 'situacion' => 'Riesgo de caídas', 'recomendacion' => 'Evitar.', 'condicionada' => true, 'source' => 'aemps')),
);
$a = vr_alertas($productos, array(), $contra);
$tipos = array_map(function ($x) { return $x['tipo']; }, $a);
ok(in_array('embarazo', $tipos, true) && in_array('lactancia', $tipos, true), 'embarazo y lactancia como tipos propios');
ok(in_array('enfermedad', $tipos, true) && in_array('precaucion', $tipos, true), 'enfermedad y precaución');
ok(in_array('alergia', $tipos, true) && in_array('mayores', $tipos, true), 'alergia y mayores');
$al = $a[array_search('alergia', $tipos, true)];
ok($al['productos'] === array('Perifar 400', 'Dolostop Plus') && $al['gravedad'] === 'importante', 'alergia a todos los productos con la sustancia');
$em = $a[array_search('embarazo', $tipos, true)];
ok($em['productos'] === array('Coumadin 5 mg') && $em['gravedad'] === 'importante', 'embarazo por producto');
$ge = $a[array_search('mayores', $tipos, true)];
ok(strpos($ge['detalle'][0]['texto'], 'Riesgo de caídas') !== false && $ge['detalle'][0]['fuente'] === 'AEMPS', 'mayores con texto AEMPS');
ok($a[0]['gravedad'] === 'importante', 'ordenadas por gravedad');

echo "vr_resumen / vr_mensaje\n";
$r = vr_resumen($a);
ok($r['total'] === count($a) && $r['importantes'] >= 3, 'cuenta total e importantes');
ok(vr_resumen(array()) === array('total' => 0, 'importantes' => 0, 'por_tipo' => array()), 'vacío');
foreach (array('interaccion', 'duplicidad', 'alergia', 'embarazo', 'lactancia', 'enfermedad', 'precaucion', 'mayores') as $t) {
    foreach (array('importante', 'moderada', 'leve', 'incierta') as $g) {
        $m = vr_mensaje($t, $g);
        if (!$m || strpos($m['texto'], 'consultalo con tu médico') === false) { ok(false, "mensaje $t/$g cierra con la consulta al médico"); }
    }
}
ok(true, 'toda la matriz de mensajes cierra con la consulta al médico');
ok(vr_mensaje('interaccion', 'importante')['titulo'] !== vr_mensaje('interaccion', 'leve')['titulo'], 'títulos distintos por gravedad');

echo $fallos ? "\n$fallos fallo(s)\n" : "\ntodo ok\n";
exit($fallos ? 1 : 0);
