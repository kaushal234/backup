<?php
ob_start();
?>

<p class="item_dear">Apreciable <?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>Dando seguimiento a su solicitud de ‘<?= $sso ?> en llamada’ iniciada el día <?= $this->getOpenDate() ?><?php if($this->isEquipmentSet()): ?> apara su equipo <?= $sso ?> <?= $this->itsHeader['type'] ?> <?= $this->itsHeader['model'] ?> ubicada en el aeropuerto <?= $this->itsHeader['airport_code'] ?><?php  endif; ?>, nos complace informarle que su número de reporte TOC#<?= $this->itsID ?>se encuentra resuelto. Puede obtener mayores detalles ingresando a la siguiente página de Internet<a href="https://www.tld-gse.com/extranet/index.php?m[0]=toc&m[1]=view&id=<?= $this->itsID ?>">página de Internet</a>.</p>
<p>Esperamos que la calidad de nuestro servicio haya sido de su entera satisfacción; le agradecemos pueda tomarse un momento (1 minuto) para responder a la siguiente <a href="<?= $tokenLink ?>">encuesta</a> que le permitirá calificar su experiencia con el servicio de <?= $sso ?>.</p>
<p>En caso de que tenga alguna pregunta o consulta adicional por favor póngase en contacto con nosotros a la brevedad posible. Si no recibiéramos comunicación adicional al respecto durante los próximos 14 días, consideraríamos este caso como resuelto.</p>
<p>Agradecemos la confianza depositada en <?= $sso ?> para sus operaciones.</p>
<p>Cordialmente,</p>
<p>El equipo de Servicio de <?= $sso ?></p>

<?php
return ob_get_clean();
?>
