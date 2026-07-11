<?php
//require 'class.phpmailer.php';
//require 'class.smtp.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer.php';
require 'SMTP.php';

function sendEmail($sendEmail){

	$mail = new PHPMailer();  // create a new object
	$mail->CharSet = "UTF-8";
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only
      // authentication enabled
		$mail->SMTPOptions = array(
			'ssl' => array(
			'verify_peer' => false,
			'verify_peer_name' => false,
			'allow_self_signed' => true
			)
		);
		$mail->SMTPAuth = true;
    $mail->SMTPSecure = 'ssl';
    $mail->Host = 'mail01.iwtg.com';
    $mail->Port = 465;
    $mail->Username = 'notificaciones@doctorconsultas.com';
    $mail->Password = 'no1953fdc1';
    $mail->SetFrom('notificaciones@doctorconsultas.com', 'Recetalia - Lanzamiento');
    $mail->Subject = $sendEmail['subject'];
	$mail->IsHTML(true); // El correo se envía como HTML
	$mail->Body = $sendEmail['body'];
	//$mail->Body = "a ver";

    $mail->AddAddress("hello@doctorconsultas.com");
	$mail->AddBCC("soporte@avisil.com");
	//$mail->AddBCC("maurice@play.com.uy");

    if(!$mail->Send()) {
        //echo 'Mail error: '.$mail->ErrorInfo;
        return false;
    } else {
        //echo 'Message sent!';
        return true;
    }

}

?>
