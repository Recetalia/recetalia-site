<?php

if(!$_POST) exit;

// Email address verification, do not edit.
function isEmail($email) {
	return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i",$email));
}

if (!defined("PHP_EOL")) define("PHP_EOL", "\r\n");

$name     = $_POST['name'];
$lastname  = $_POST['lastname'];
$email    = $_POST['email'];
$phone    = $_POST['phone'];
$cjp = $_POST['cjp'];
$speciality = $_POST['speciality'];
$comment = $_POST['comment'];

if(trim($name) == '') {
	echo '<div class="alert alert-error">Ingresa un nombre.</div>';
	exit();
} else if(trim($lastname) == '') {
	echo '<div class="alert alert-error">Ingresa un Apellidoss.'.$_POST['lastname'].'</div>';
	exit();
} else if(trim($cjp) == '') {
	echo '<div class="alert alert-error">Ingresa un Número de CJP.</div>';
	exit();
} else if(trim($phone) == '') {
	echo '<div class="alert alert-error">Ingresa un Teléfono.</div>';
	exit();
} else if(trim($comment) == '') {
	echo '<div class="alert alert-error">Ingresa un Comentario.</div>';
	exit();
} else if(trim($email) == '') {
	echo '<div class="alert alert-error">Ingresa una direccion de email.</div>';
	exit();

} else if(!isEmail($email)) {
	echo '<div class="alert alert-error">Ingresa una direccion de email válida.</div>';
	exit();
} 


require '_email-sender.php';

$sendEmail = array();
$sendEmail['subject']	= "Desde el Inversores de Recetalia.com";
$sendEmail['body'] = "Datos ingresados:<br /><br />
 Nombre: ".$name." ".$lastname." <br />
 Tel: ".$phone."   <br />
 Mail:  ".$email."   <br />
 CJP:  ".$cjp."   <br />
 Epecialidad:  ".$speciality."   <br />
 Comentarios:  ".$comment;

if(sendEmail($sendEmail)){
		echo "<div class='alert alert-success'>";
		echo "<h3>Email enviado Exitosamente.</h3>";
		echo "<p>Muchas Gracias <strong>$name</strong>, Su mensaje ha sido enviado exitosamente.</p>";
		echo "</div>";
}else{
	echo "El envio fallo";
}



