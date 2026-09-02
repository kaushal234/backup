<?php
ob_start();
?>

<p class="item_dear">Apreciable  <?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>Dando seguimiento a su solicitud de soporte técnico de <?= $sso ?> el día <?= $this->getOpenDate() ?>, le informamos que su caso ha sido canalizado por nuestro equipo de servicio técnico.</p>
<p>Por favor tenga siempre a la mano su número de reporte TOC#<?= $this->itsID ?>  para agilizar cualquier comunicación al respecto.</p>
<p>Estaremos confirmando tan pronto su caso sea resuelto. Asimismo, es muy probable que reciba comunicación de nuestro equipo detallando las actividades y acciones que se estén tomando para dar solución a su caso.</p>
<p>Mientras tanto, nuestro equipo de ‘<?= $sso ?> en llamada’ se mantiene a su disposición en caso de que tenga alguna duda o cuestionamientos al respecto.</p>
<p>Cordialmente,</p>
<p>El equipo de Servicio de <?= $sso ?></p>

<?php
return ob_get_clean();
?>
