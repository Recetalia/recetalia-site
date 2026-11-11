<?php
// Llamadas a Consilio del verificador (server-side, misma API key que el vademécum).
//   POST /interactions       products[{id, substances[]}]
//   POST /contraindications  drugs[] + profile{sexo, edad, embarazo, lactancia, alergias[], patologias_icd10[]}
//   GET  /conditions/search  autocompletado de enfermedades (CIE-10 en castellano)

function vr_consilio($metodo, $ruta, $cuerpo = null, $timeout = 8) {
    $base = rtrim((string)getenv('SITE_CONSILIO_URL'), '/');
    if ($base === '') return null;
    $ch = curl_init($base . $ruta);
    $headers = array('X-API-Key: ' . getenv('SITE_CONSILIO_API_KEY'), 'Accept: application/json');
    $opts = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => $timeout);
    if ($metodo === 'POST') {
        $headers[] = 'Content-Type: application/json';
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
    }
    $opts[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($ch, $opts);
    $r = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (PHP_VERSION_ID < 80000) { curl_close($ch); }
    if ($r === false || $codigo !== 200) {
        error_log("verificador: Consilio $ruta respondió $codigo");
        return null;
    }
    $d = json_decode($r, true);
    return is_array($d) ? $d : null;
}

// [inter, contra] o null si Consilio no respondió (no se guarda la consulta en ese caso).
function vr_consultar(array $productos, array $datos) {
    $prods = vr_productos_consilio($productos);
    $inter = count($prods) >= 2 ? vr_consilio('POST', '/interactions', array('products' => $prods)) : array();
    if ($inter === null) return null;
    $perfil = array(
        'sexo' => $datos['sexo'], 'edad' => $datos['edad'],
        'embarazo' => $datos['embarazo'], 'lactancia' => $datos['lactancia'],
        'alergias' => $datos['alergias'],
        'patologias_icd10' => array_map(function ($p) { return $p['codigo']; }, $datos['patologias']),
    );
    $contra = vr_consilio('POST', '/contraindications', array('drugs' => vr_drogas($productos), 'profile' => $perfil));
    if ($contra === null) return null;
    return array($inter, $contra);
}
