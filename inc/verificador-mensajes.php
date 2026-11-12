<?php
// Mensajes para pacientes del verificador de interacciones: uno por tipo de alerta × gravedad.
// Son los ÚNICOS textos "en lenguaje simple": no se genera texto por alerta. Los redactó el equipo y
// los tiene que aprobar un médico (Daniel Carbajal) antes de PROD (VR_MENSAJES_APROBADOS). Desde 2026-11-11 la
// página ya no muestra la marca de borrador (pedido de Pablo); la aprobación sigue siendo requisito de PROD. Cambiar un texto = subir VR_MENSAJES_VERSION (se guarda
// con cada consulta). Spec: doc/plans/2026-11-11-verificador-pacientes-design.md.

const VR_MENSAJES_VERSION = '2026-11-11-borrador';
const VR_MENSAJES_APROBADOS = false;

const VR_CIERRE = 'No dejes ni cambies ningún medicamento por tu cuenta: consultalo con tu médico o químico farmacéutico.';

// [tipo][gravedad] => [titulo, texto sin el cierre]. 'incierta' = la fuente registra el problema sin gravedad.
const VR_MENSAJES = array(
    'interaccion' => array(
        'importante' => array('Interacción importante',
            'Tomar estos medicamentos juntos puede causar un efecto no deseado importante para tu salud. A veces se indican igual, con controles o ajustando la dosis.'),
        'moderada' => array('Interacción moderada',
            'Tomar estos medicamentos juntos puede cambiar cómo actúa alguno de ellos o aumentar sus efectos no deseados. Muchas veces se pueden usar juntos con algunas precauciones.'),
        'leve' => array('Interacción leve',
            'Estos medicamentos pueden influirse entre sí de forma leve. En general no causa problemas, pero conviene que tu médico lo sepa.'),
        'incierta' => array('Posible interacción',
            'Las fuentes registran una posible interacción entre estos medicamentos, pero no indican qué tan importante es.'),
    ),
    'duplicidad' => array(
        'importante' => array('El mismo medicamento repetido',
            'Estos productos contienen el mismo principio activo. Tomarlos juntos puede hacer que recibas más dosis de la indicada.'),
        'moderada' => array('Medicamentos del mismo tipo',
            'Estos productos son del mismo grupo de medicamentos y suelen tener efectos parecidos. Tomarlos juntos puede sumar efectos no deseados.'),
        'leve' => array('Medicamentos del mismo tipo',
            'Estos productos son del mismo grupo de medicamentos. Conviene que tu médico sepa que tomás ambos.'),
        'incierta' => array('Posible duplicación',
            'Estos productos podrían tener efectos parecidos. Conviene que tu médico sepa que tomás ambos.'),
    ),
    'alergia' => array(
        'importante' => array('Coincide con una alergia que indicaste',
            'Este medicamento contiene una sustancia a la que dijiste ser alérgico o alérgica.'),
        'moderada' => array('Posible relación con una alergia que indicaste',
            'Este medicamento podría estar relacionado con una alergia que indicaste.'),
        'leve' => array('Posible relación con una alergia que indicaste',
            'Este medicamento podría estar relacionado con una alergia que indicaste.'),
        'incierta' => array('No pudimos revisar una alergia que indicaste',
            'No reconocimos una de las alergias que escribiste, así que no pudimos compararla con tus medicamentos.'),
    ),
    'embarazo' => array(
        'importante' => array('Precaución durante el embarazo',
            'Las fuentes indican que este medicamento puede no ser adecuado durante el embarazo.'),
        'moderada' => array('Precaución durante el embarazo',
            'Este medicamento requiere precaución durante el embarazo.'),
        'leve' => array('Precaución durante el embarazo',
            'Este medicamento requiere precaución durante el embarazo.'),
        'incierta' => array('Precaución durante el embarazo',
            'Las fuentes mencionan este medicamento en relación con el embarazo.'),
    ),
    'lactancia' => array(
        'importante' => array('Precaución durante la lactancia',
            'Las fuentes indican que este medicamento puede no ser adecuado durante la lactancia.'),
        'moderada' => array('Precaución durante la lactancia',
            'Este medicamento requiere precaución durante la lactancia.'),
        'leve' => array('Precaución durante la lactancia',
            'Este medicamento requiere precaución durante la lactancia.'),
        'incierta' => array('Precaución durante la lactancia',
            'Las fuentes mencionan este medicamento en relación con la lactancia.'),
    ),
    'enfermedad' => array(
        'importante' => array('Puede no ser adecuado con una enfermedad que indicaste',
            'Las fuentes indican que este medicamento puede no ser adecuado para personas con una de las enfermedades que indicaste.'),
        'moderada' => array('Precaución con una enfermedad que indicaste',
            'Este medicamento requiere precaución en personas con una de las enfermedades que indicaste.'),
        'leve' => array('Precaución con una enfermedad que indicaste',
            'Este medicamento requiere precaución en personas con una de las enfermedades que indicaste.'),
        'incierta' => array('Relación con una enfermedad que indicaste',
            'Las fuentes mencionan este medicamento en relación con una de las enfermedades que indicaste.'),
    ),
    'precaucion' => array(
        'importante' => array('Precaución con una enfermedad que indicaste',
            'Este medicamento requiere precaución en personas con una de las enfermedades que indicaste.'),
        'moderada' => array('Precaución con una enfermedad que indicaste',
            'Este medicamento requiere precaución en personas con una de las enfermedades que indicaste.'),
        'leve' => array('Precaución con una enfermedad que indicaste',
            'Este medicamento requiere precaución en personas con una de las enfermedades que indicaste.'),
        'incierta' => array('Precaución con una enfermedad que indicaste',
            'Este medicamento requiere precaución en personas con una de las enfermedades que indicaste.'),
    ),
    'mayores' => array(
        'importante' => array('Precaución en personas mayores',
            'En personas mayores este medicamento puede requerir controles, una dosis menor o una alternativa, según su situación.'),
        'moderada' => array('Precaución en personas mayores',
            'En personas mayores este medicamento puede requerir controles, una dosis menor o una alternativa, según su situación.'),
        'leve' => array('Precaución en personas mayores',
            'En personas mayores conviene revisar este medicamento con el médico.'),
        'incierta' => array('Precaución en personas mayores',
            'En personas mayores conviene revisar este medicamento con el médico.'),
    ),
);

// Resultado sin hallazgos: nunca "es seguro".
const VR_SIN_HALLAZGOS = 'No encontramos interacciones ni advertencias en nuestras fuentes para estos medicamentos y los datos que indicaste. Eso no significa que no existan: las fuentes no cubren todos los casos. Ante cualquier duda, consultalo con tu médico o químico farmacéutico.';

// Disclaimer destacado del email (pedido de Daniel, 2026-11-12): el detalle sólo llega por email.
const VR_EMAIL_DISCLAIMER = 'La información de este informe es genérica y orientativa: surge de fuentes públicas de referencia, no tiene en cuenta toda tu situación de salud y no reemplaza la consulta médica. Siempre consultá a tu médico o químico farmacéutico antes de tomar, dejar o cambiar un medicamento.';

const VR_AVISO = 'Esta herramienta es informativa y no reemplaza la consulta con un profesional de la salud. ' . VR_CIERRE;

// Cambiar el texto de consentimiento (medicamentos/interacciones/index.php) = subir la versión.
// 2026-11-11b: consentimiento por acción afirmativa (texto junto al botón, sin casilla).
const VR_CONSENTIMIENTO_VERSION = '2026-11-11b';
