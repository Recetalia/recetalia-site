<?php
// Política de privacidad. El texto legal va tal cual; sólo cambia el layout (inc/layout.php).
require_once __DIR__ . '/../inc/layout.php';
rc_head(array(
    'title' => 'Política de privacidad | Recetalia',
    'description' => 'Política de privacidad de Recetalia (Ley N° 18.331 de protección de datos personales).',
    'canonical' => rtrim(getenv('SITE_PUBLIC_URL') ?: 'https://recetalia.com', '/') . '/privacy/',
));
?>
<main id="contenido">
    <section class="rc-pagina-cabeza" aria-labelledby="pagina-titulo">
        <div class="rc-container rc-angosto">
            <nav class="rc-migas" aria-label="Ruta de navegación">
                <ol>
                    <li><a href="/">Inicio</a><?php echo rc_icono('chevron-right'); ?></li>
                    <li><span aria-current="page">Política de privacidad</span></li>
                </ol>
            </nav>
            <h1 id="pagina-titulo">Política de privacidad</h1>
        </div>
    </section>
    <section class="rc-seccion rc-seccion-pegada">
        <div class="rc-container rc-angosto">
            <div class="rc-prosa">
<h2 class="h5 text-left">RECETALIA</h2><br>
<h3 class="h5 text-left">Política de privacidad.</h3><br>

<h3 class="mt-3 h5 text-left">POLÍTICA DE PRIVACIDAD USUARIOS (comprende a todos los perfiles de usuario, ejemplo “usuario paciente” y “usuario médico prestador”).</h3>

<p class="text-left">En cumplimiento de la ley N° 18.331 sobre protección de datos personales, a continuación facilitamos información ampliada en la presente Política de Privacidad.<br><br>
<strong class="mt-2">Quién es el responsable del tratamiento de sus datos </strong><br>
Identidad:<br>
SUNFOLK S.A. (En adelante RECETALIA)<br>
Dirección: Luis Alberto de Herrera 1248, Torre III.<br>
Correo electrónico: hello@RECETALIA.com <br><br>
<strong class="mt-2">Con qué finalidad tratamos sus datos personales</strong><br>
En RECETALIA tratamos la información que nos facilitan las personas interesadas con el fin de prestar los servicios entre el usuario (paciente) y los profesionales médicos y/o centros médicos incluidos en el directorio de la plataforma.<br>
Para la realización de estos servicios el usuario debe facilitar datos personales como su nombre, email o número de teléfono móvil. <br>
RECETALIA es la propietaria de la plataforma, pero el profesional médico o centro médico es el encargado y responsable del tratamiento de datos. <br>El médico es el responsable de solicitar a sus pacientes el consentimiento para el tratamiento de los datos recabados durante la consulta e ingresados en la plataforma y de garantizar el correcto cumplimiento de la normativa de privacidad. En ningún caso RECETALIA será responsable de la omisión por parte de un profesional o centro médico de su obligación de cumplimiento de la reglamentación vigente.<br>
Los servicios de RECETALIA incluyen el envío de recordatorios. También se realizan envíos de otras comunicaciones, por ejemplo, como notificaciones o campañas promocionales. <br><br>
<strong class="mt-2">Por cuánto tiempo conservaremos sus datos</strong><br>
Los datos personales proporcionados se conservarán mientras el usuario permanezca activo en la plataforma y durante los plazos necesarios para dar cumplimiento a las obligaciones legales. <br><br>
<strong class="mt-2">La legitimación para el tratamiento de sus datos</strong><br>
La base legal para el tratamiento de sus datos es el consentimiento del interesado.<br><br>
<strong class="mt-2">Destinatarios a quién se comunicarán sus datos</strong><br>
Los datos se comunicarán a los médicos, profesionales sanitarios y/o centros médicos, farmacias, gobierno en el caso de ser exigibles. Asimismo y a los solos efectos del mantenimiento del servicio, podrán tener acceso a estos datos los encargados de tratamiento que dan distinto soporte a RECETALIA como Legal, gestión anonimizada de diagnóstico médico, alojamiento de datos, gestión de bases de datos y de comunicaciones, así como a los terceros necesarios para dar cumplimiento a las obligaciones legales.<br><br>
<strong class="mt-2">Cuáles son sus derechos cuando nos facilita sus datos</strong><br>
Cualquier persona tiene derecho a obtener información sobre si en RECETALIA estamos tratando datos personales que les conciernen, o no.<br>
Las personas interesadas tienen derecho a acceder a sus datos personales, así como a solicitar la rectificación de los datos inexactos o, en su caso, solicitar su supresión cuando, entre otros motivos, los datos ya no sean necesarios para los fines que fueron recogidos.<br><br>
<strong class="mt-2">Cómo hemos obtenido sus datos</strong><br>
El registro del usuario/paciente, el cual será creado por el Médico al momento de prescribir el medicamento o por el propio Usuario/paciente y una contraseña automática o elegida por el paciente, los cuales le identificarán y habilitarán personalmente para poder tener acceso a los servicios.<br><br>
<strong class="mt-2">Más información sobre el tratamiento de sus datos</strong><br>
Cuando se requiera obtener información por su parte, siempre se solicitará al Usuario que la proporcione voluntariamente prestando su consentimiento de forma expresa a través de los medios habilitados para ello.<br>
El tratamiento de los datos recabados a través de los formularios de recogida de datos del sitio web u otras vías (a modo de ejemplo y sin limitarse: redes sociales, emails, comunicación solicitando datos, etc.), quedará incorporado al Registro de Actividades de Tratamiento del cual es responsable RECETALIA<br>
RECETALIA trata los datos de forma confidencial y adopta las medidas técnicas y organizativas a su alcance para garantizar el nivel de seguridad adecuado al tratamiento, en cumplimiento de lo requerido por el Art. 10 de la ley de protección de datos personales.<br>
No obstante, no puede garantizar la absoluta invulnerabilidad de los sistemas, por tanto, no asume ninguna responsabilidad por los daños y perjuicios derivados de alteraciones que terceros puedan causar en los sistemas informáticos, documentos electrónicos o ficheros del usuario.<br>
Si opta por abandonar nuestro sitio web a través de enlaces a sitios web no pertenecientes a nuestra entidad, RECETALIA no se hará responsable de las políticas de privacidad de dichos sitios web ni de las cookies que éstos puedan almacenar en el ordenador del usuario.<br>
Nuestra política con respecto al envío de nuestros propios correos electrónicos se centra en remitir únicamente comunicaciones que usted haya solicitado recibir. Si prefiere no recibir estos mensajes por correo electrónico le ofreceremos a través de los mismos la posibilidad de ejercer su derecho de supresión y renuncia a la recepción de estos mensajes.<br>


</p>















                    

<h3 class="mt-3 h5 text-left" id="verificador">VERIFICADOR DE INTERACCIONES PARA PACIENTES (recetalia.com/medicamentos/interacciones)</h3>
<?php // Agregado 2026-11-12 (pendiente de revisión legal; decisión de Pablo: sale y se verifica después). ?>
<p class="text-left">Esta sección se aplica a quienes usan el verificador de interacciones de medicamentos del sitio recetalia.com, que funciona sin cuenta de usuario y es independiente de la receta digital.<br><br>
<strong>Qué datos guardamos.</strong> Tu email; tu edad y sexo; si indicaste embarazo o lactancia; las alergias y enfermedades que hayas cargado; los medicamentos que consultaste; un resumen del resultado (cantidad y tipo de alertas); la fecha de la consulta y la fecha y versión del texto de consentimiento que aceptaste. Varios de estos datos son <strong>datos de salud</strong>, que la Ley N° 18.331 considera datos sensibles: los tratamos únicamente con tu consentimiento expreso, que das al tocar «Ver interacciones» después de leer el aviso correspondiente.<br><br>
<strong>Para qué los usamos.</strong> Para calcular el resultado de tu consulta y enviártelo por email. Si marcaste la opción de novedades, también para enviarte novedades de Recetalia por email; podés dejar de recibirlas en cualquier momento. No usamos estos datos para tomar decisiones sobre vos ni los vendemos.<br><br>
<strong>Con quién los compartimos.</strong> No los cedemos a terceros. Para calcular el resultado enviamos a nuestro motor de información farmacológica (Consilio) los medicamentos y los datos clínicos de la consulta, sin tu email ni ningún dato que te identifique. El email se envía a través de nuestro proveedor de correo.<br><br>
<strong>Dónde y por cuánto tiempo.</strong> Se guardan en la base de datos de Recetalia, separados de la información de la receta digital y con acceso restringido. Los conservamos durante <strong>2 años desde tu última consulta</strong>; pasado ese plazo se eliminan. Si antes pedís que los borremos, los eliminamos en ese momento.<br><br>
<strong>Tus derechos.</strong> Podés pedir acceso, rectificación, actualización o supresión de tus datos. Para borrarlos, usá el enlace «Borrar mis datos» que llega en cada email del verificador (elimina tus datos y todas tus consultas), o escribinos a <a href="mailto:hello@recetalia.com">hello@recetalia.com</a>. También podés presentar un reclamo ante la Unidad Reguladora y de Control de Datos Personales (URCDP).<br><br>
<strong>Carácter de la información.</strong> El resultado del verificador es información genérica y orientativa basada en fuentes públicas de referencia; no reemplaza la consulta con un profesional de la salud.</p>
            </div>
        </div>
    </section>
</main>
<?php rc_pie('', true); ?>
