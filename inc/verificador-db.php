<?php
// Persistencia del verificador (schema `publico`) con el rol `site_publico`: escribe sólo ahí y lee
// el catálogo DNMA. Credenciales: SITE_PUBLICO_DB_USER / SITE_PUBLICO_DB_PASS (host/base de SITE_DB_*).
require_once __DIR__ . '/vademecum-db.php';

function vr_pdo() {
    $dsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s;sslmode=require',
        getenv('SITE_DB_HOST'),
        (int)(getenv('SITE_DB_PORT') ?: 25060),
        getenv('SITE_DB_NAME') ?: 'recetalia'
    );
    return new PDO($dsn, getenv('SITE_PUBLICO_DB_USER') ?: 'site_publico', getenv('SITE_PUBLICO_DB_PASS'), array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ));
}

function vr_uuid() {
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
}

// Productos de la lista → [{id, nombre, sustancias[]}], en el orden pedido; descarta ids inexistentes.
function vr_resolver_productos(PDO $pdo, array $ids) {
    $out = array();
    foreach ($ids as $id) {
        $p = vd_producto($pdo, $id);
        if (!$p) continue;
        $out[] = array('id' => (string)$p['amp_id'], 'nombre' => vd_nombre($p['amp_dsc']),
            'generico' => $p['vmp_dsc'], 'sustancias' => vd_sustancias($pdo, $p['vmp_id']));
    }
    return $out;
}

// Alta o actualización de la persona por email; devuelve [id, baja_token].
function vr_guardar_persona(PDO $pdo, $email, $novedades) {
    $st = $pdo->prepare('SELECT id, baja_token FROM publico.persona WHERE lower(email) = lower(:e)');
    $st->execute(array('e' => $email));
    $fila = $st->fetch();
    if ($fila) {
        $pdo->prepare("UPDATE publico.persona SET consentimiento_at = (now() AT TIME ZONE 'UTC'),
                consentimiento_version = :v, novedades = :n WHERE id = :id")
            ->execute(array('v' => VR_CONSENTIMIENTO_VERSION, 'n' => $novedades ? 'true' : 'false', 'id' => $fila['id']));
        return array($fila['id'], $fila['baja_token']);
    }
    $id = vr_uuid();
    $token = bin2hex(random_bytes(24));
    $pdo->prepare("INSERT INTO publico.persona (id, email, consentimiento_at, consentimiento_version, novedades, baja_token)
            VALUES (:id, :e, (now() AT TIME ZONE 'UTC'), :v, :n, :t)")
        ->execute(array('id' => $id, 'e' => $email, 'v' => VR_CONSENTIMIENTO_VERSION,
            'n' => $novedades ? 'true' : 'false', 't' => $token));
    return array($id, $token);
}

function vr_guardar_consulta(PDO $pdo, $personaId, array $datos, array $productos, array $resumen) {
    $j = function ($v) { return json_encode($v, JSON_UNESCAPED_UNICODE); };
    $pdo->prepare('INSERT INTO publico.consulta (id, persona_id, edad, sexo, embarazo, lactancia, alergias, patologias,
            productos, resumen, mensajes_version)
            VALUES (:id, :p, :edad, :sexo, :emb, :lac, CAST(:al AS jsonb), CAST(:pa AS jsonb), CAST(:pr AS jsonb),
            CAST(:re AS jsonb), :mv)')
        ->execute(array(
            'id' => vr_uuid(), 'p' => $personaId, 'edad' => $datos['edad'], 'sexo' => $datos['sexo'],
            'emb' => $datos['embarazo'] ? 'true' : 'false', 'lac' => $datos['lactancia'] ? 'true' : 'false',
            'al' => $j($datos['alergias']), 'pa' => $j($datos['patologias']),
            'pr' => $j(array_map(function ($p) { return array('id' => $p['id'], 'nombre' => $p['nombre']); }, $productos)),
            're' => $j($resumen), 'mv' => VR_MENSAJES_VERSION,
        ));
}

// Retención (política de privacidad, § verificador): 2 años desde la última consulta. Se corre después de cada
// consulta nueva: la tabla es chica y así no depende de un cron. Devuelve cuántas personas borró.
function vr_purgar_vencidos(PDO $pdo) {
    $st = $pdo->prepare("DELETE FROM publico.persona p
        WHERE p.creado < (now() AT TIME ZONE 'UTC') - interval '2 years'
          AND NOT EXISTS (SELECT 1 FROM publico.consulta c
                          WHERE c.persona_id = p.id AND c.creado >= (now() AT TIME ZONE 'UTC') - interval '2 years')");
    $st->execute();
    return $st->rowCount();
}

// Borra la persona y sus consultas. Devuelve true si existía.
function vr_baja(PDO $pdo, $token) {
    if (!preg_match('/^[0-9a-f]{48}$/', (string)$token)) return false;
    $st = $pdo->prepare('DELETE FROM publico.persona WHERE baja_token = :t');
    $st->execute(array('t' => $token));
    return $st->rowCount() > 0;
}
