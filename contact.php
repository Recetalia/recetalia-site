<?php
// Página de contacto vieja del template: el formulario vive en la portada (#ContactSet). El envío
// sigue yendo a mailer/contact.php, que no se toca.
header("Location: /#ContactSet", true, 301);
