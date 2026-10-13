<?php
putenv('SITE_FORM_SECRET=test');
require __DIR__ . '/../inc/antispam.php';
$ok = 0; $fail = 0;
function caso($nombre, $post, $campos, $esperado, $ficha = true) {
    global $ok, $fail;
    $_POST = $post; $_SERVER['REMOTE_ADDR'] = $nombre; // IP distinta por caso
    $r = antispam_motivo($campos, $ficha);
    $pasa = ($r === $esperado);
    $pasa ? $ok++ : $fail++;
    printf("%-4s %-28s → %s\n", $pasa ? 'OK' : 'FAIL', $nombre, $r ?? 'pasa');
}
$vieja = (time() - 60); $ft = $vieja . '.' . hash_hmac('sha256', (string)$vieja, 'test');
$leg = ['name'=>'Ana Pérez','email'=>'ana@x.com','comment'=>'Hola, quiero info para mi farmacia.','ft'=>$ft,'website'=>''];
caso('legitimo', $leg, ['Ana Pérez','ana@x.com','Hola, quiero info para mi farmacia.'], null);
caso('campo-trampa', ['website'=>'x']+$leg, ['a'], 'campo-trampa');
caso('sin-ficha', array_diff_key($leg,['ft'=>1]), ['a'], 'sin-ficha');
caso('ficha-falsa', ['ft'=>$vieja.'.abc']+$leg, ['a'], 'ficha-invalida');
$nueva = time(); caso('rapido', ['ft'=>$nueva.'.'.hash_hmac('sha256',(string)$nueva,'test')]+$leg, ['a'], 'demasiado-rapido');
$v = time()-90000; caso('vencida', ['ft'=>$v.'.'.hash_hmac('sha256',(string)$v,'test')]+$leg, ['a'], 'ficha-vencida');
caso('link', $leg, ['seo https://spam.ru'], 'link');
caso('cirilico (el spam real)', $leg, ['prodvijenie saitov v moskve_tcOl','Нужен толковый специалист по продвижению сайтов в Москве'], 'alfabeto');
caso('lanzamiento sin ficha', ['website'=>''], ['Juan','juan@x.com'], null, false);
$_POST = $leg; $_SERVER['REMOTE_ADDR'] = 'rl'; $r=[]; for ($i=0;$i<4;$i++) $r[] = antispam_motivo(['x'], true) ?? 'pasa';
$e = ['pasa','pasa','pasa','limite-ip']; ($r === $e) ? $ok++ : $fail++; echo (($r===$e)?'OK  ':'FAIL')." limite 3/h                     → ".implode(',', $r)."\n";
echo "html: ", antispam_html('<a href="x">link</a>'), "\n";
echo "\n$ok OK, $fail FAIL\n"; exit($fail ? 1 : 0);
