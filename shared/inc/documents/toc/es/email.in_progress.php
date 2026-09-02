<?php
ob_start();
?>

<p class="item_dear">Estimado <?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>Dando seguimiento a su solicitud enviada a « <?=$sso?> en Llamada » el día <?= $this->getOpenDate() ?> le informamos que su caso ha sido investigado por nuestro departamento de Servicio llevando a cabo las siguientes acciones:</p>
<p><b><?= $txt1 ?></b></p>
<p>Esperamos sinceramente que el soporte recibido sea satisfactorio para usted. Por favor visite nuestro sitio en Internet <a href="https://www.tld-gse.com/extranet">support website</a> para encontrar información de productos, manuales, tutoriales, solución de problemas, y mucho mas.</p>
<p>Mientras tanto, nuestro asesor de <?=$sso?> en Llamada, se encuentra siempre disponible en caso de que tenga alguna otra duda o pregunta.</p>
<p>Sinceramente,</p>
<p>El Equipo de Servicio de <?=$sso?></p>

<?php
return ob_get_clean();
?>
