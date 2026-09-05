<?php
// Funciones puras de farmacias-habilitadas.php. Sin DB ni salida, para poder
// probarlas con tests/farmacias-habilitadas.test.php. Compatibles con PHP 7.4.

const FH_SQL = "
    SELECT pharmacy.id, pharmacy.name, pharmacy.addressStreet, pharmacy.addressNumber,
           pharmacy.phone, regions.name AS regionName, localities.name AS localityName
    FROM pharmacy
    JOIN localities ON localities.id = pharmacy.addressLocalityId
    JOIN regions ON regions.id = localities.region_id
    WHERE pharmacy.status = 'ACTIVE'
    ORDER BY pharmacy.name ASC";

// Minúsculas, sin tildes, espacios colapsados. Misma regla que el normalizar() del
// script de la página: lo que se compara tiene que salir igual en PHP y en JS.
function fh_normalizar($texto) {
    $texto = mb_strtolower((string)$texto, 'UTF-8');
    $texto = strtr($texto, array(
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
    ));
    return trim(preg_replace('/\s+/', ' ', $texto));
}

// Columna `phone` = JSON {"national": "...", "international": "..."} o basura/NULL.
function fh_telefono($phoneJson) {
    $tel = json_decode((string)$phoneJson, true);
    if (!is_array($tel) || empty($tel['national'])) {
        return null;
    }
    $national = (string)$tel['national'];
    $international = !empty($tel['international']) ? (string)$tel['international'] : $national;
    return array('national' => $national, 'international' => $international);
}

// Filas del SQL → ['total' => N, 'regiones' => [nombre => ['nombre', 'cantidad', 'farmacias' => [...]]]]
// Regiones ordenadas alfabéticamente sin tildes; farmacias en el orden del SQL (por nombre).
function fh_agrupar(array $filas) {
    $regiones = array();
    foreach ($filas as $fila) {
        $region = trim((string)$fila['regionName']);
        $direccion = trim(trim((string)$fila['addressStreet']) . ' ' . trim((string)$fila['addressNumber']));
        $localidad = trim((string)$fila['localityName']);
        $farmacia = array(
            'id' => (int)$fila['id'],
            'nombre' => trim((string)$fila['name']),
            'direccion' => $direccion,
            'localidad' => $localidad,
            'telefono' => fh_telefono($fila['phone']),
        );
        $farmacia['busqueda'] = fh_normalizar($farmacia['nombre'] . ' ' . $localidad . ' ' . $direccion);
        if (!isset($regiones[$region])) {
            $regiones[$region] = array('nombre' => $region, 'cantidad' => 0, 'farmacias' => array());
        }
        $regiones[$region]['farmacias'][] = $farmacia;
        $regiones[$region]['cantidad']++;
    }
    uksort($regiones, function ($a, $b) {
        return strcmp(fh_normalizar($a), fh_normalizar($b));
    });
    return array('total' => count($filas), 'regiones' => $regiones);
}

// Departamento seleccionado al entrar: Montevideo si existe, si no el primero.
function fh_region_inicial(array $regiones) {
    if (isset($regiones['Montevideo'])) {
        return 'Montevideo';
    }
    $claves = array_keys($regiones);
    return $claves ? (string)$claves[0] : '';
}

function fh_plural($n, $singular, $plural) {
    return $n . ' ' . ($n === 1 ? $singular : $plural);
}
