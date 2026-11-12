<?php
// Email con el resultado del verificador, por el SMTP del sitio (SITE_SMTP_*, mismo que el formulario
// de contacto). SITE_EMAIL_ALLOWLIST (PRE): lista separada por comas de casillas o "@dominio" permitidos;
// fuera de la lista no se envía (regla del proyecto: en pruebas, sólo casillas propias).
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/verificador-render.php';
require_once __DIR__ . '/../mailer/PHPMailer.php';
require_once __DIR__ . '/../mailer/SMTP.php';
require_once __DIR__ . '/../mailer/Exception.php';

function vr_email_permitido($email) {
    $lista = trim((string)getenv('SITE_EMAIL_ALLOWLIST'));
    if ($lista === '') return true; // PROD: sin lista
    foreach (array_map('trim', explode(',', strtolower($lista))) as $p) {
        if ($p === '') continue;
        if ($p[0] === '@' ? substr($email, -strlen($p)) === $p : $email === $p) return true;
    }
    return false;
}

function vr_url_publica($ruta) {
    return rtrim(getenv('SITE_PUBLIC_URL') ?: 'https://recetalia.com', '/') . $ruta;
}

// true si se envió; false si falló o quedó fuera de la allowlist.
function vr_enviar_resultado($email, array $productos, array $alertas, $bajaToken) {
    if (!vr_email_permitido($email)) {
        error_log('verificador: email fuera de SITE_EMAIL_ALLOWLIST, no se envía');
        return false;
    }
    $resumen = vr_resumen($alertas);
    $lista = implode(', ', array_map(function ($p) { return $p['nombre']; }, $productos));
    $baja = vr_url_publica('/medicamentos/interacciones/baja?t=' . $bajaToken);
    $cuerpo = '<div style="max-width:640px;margin:0 auto;font-family:Arial,sans-serif;color:#2F343B;">'
        . '<h1 style="font-size:20px;color:#212832;">Resultado de tu consulta de interacciones</h1>'
        . '<p style="font-size:15px;line-height:1.5;">Medicamentos revisados: ' . vd_e($lista) . '.</p>'
        . '<p style="font-size:17px;font-weight:bold;color:#212832;">' . vd_e(vr_titulo_resumen($resumen)) . '</p>'
        . '<div style="border:2px solid #B54708;background:#FFFAEB;padding:12px 14px;margin:0 0 16px;font-size:14px;line-height:1.5;color:#7A2E0E;font-weight:bold;">'
        . vd_e(VR_EMAIL_DISCLAIMER) . '</div>'
        . ($alertas ? vr_html_alertas($alertas, true) : '<p style="font-size:15px;line-height:1.5;">' . vd_e(VR_SIN_HALLAZGOS) . '</p>')
        . '<p style="font-size:14px;font-weight:bold;line-height:1.5;color:#212832;">' . vd_e(VR_AVISO) . '</p>'
        . '<p style="font-size:12px;line-height:1.5;color:#5B636E;">' . vd_e(vr_fuentes_usadas($alertas)) . '</p>'
        . '<p style="font-size:12px;line-height:1.5;color:#5B636E;">Recibiste este email porque lo pediste en recetalia.com. '
        . '<a href="' . vd_e($baja) . '" style="color:#0B7A88;">Borrar mis datos</a>.</p></div>';

    $m = new PHPMailer();
    $m->CharSet = 'UTF-8';
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->SMTPSecure = 'ssl';
    $m->Host = getenv('SITE_SMTP_HOST') ?: 'mail.iwtg.com';
    $m->Port = 465;
    $m->Timeout = 10;
    $m->Username = getenv('SITE_SMTP_USER');
    $m->Password = getenv('SITE_SMTP_PASS');
    $m->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));
    $m->setFrom(getenv('SITE_SMTP_FROM') ?: 'notificaciones@doctorconsultas.com', 'Recetalia');
    $m->addAddress($email);
    $m->Subject = 'Tu consulta de interacciones — Recetalia';
    $m->isHTML(true);
    $m->Body = $cuerpo;
    $m->AltBody = vr_titulo_resumen($resumen) . "\n\n" . VR_EMAIL_DISCLAIMER . "\n\n" . VR_AVISO . "\n\nBorrar mis datos: " . $baja;
    if (!$m->send()) {
        error_log('verificador: falló el envío del email: ' . $m->ErrorInfo);
        return false;
    }
    return true;
}
