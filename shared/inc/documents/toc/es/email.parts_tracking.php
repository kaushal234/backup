<?php
	ob_start();
?>

<p class="item_dear">Apreciable <?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>En relación a su solicitud de servicio registrada bajo el número de TOC# <?= $this->itsHeader['id'] ?>, le informamos que los siguientes componentes han sido embarcados:</p>
<ul>
    <li>Hoja de embalaje: #<?= $partsTracking['packing_slip'] ?></li>
    <li>Número de rastreo: #<?= $partsTracking['tracking'] ?></li>
    <?php foreach ($partsTracking['parts'] as $parts): ?>
        <ul>
            <li>Número de Parte: #<?= $parts['part_number'] ?></li>
            <li>Descripción: <?= $parts['description'] ?></li>
            <li>Cantidad embarcada: <?= $parts['quantity'] ?></li>
        </ul>
        <br>
    <?php endforeach; ?>
</ul>
<p>Por favor póngase en contacto con su asesor de TLD en llamada en caso de que tenga alguna duda o consulta.</p>
<p>Cordialmente,</p>
<p>El equipo de Servicio de TLD</p>

<?php
return ob_get_clean();
?>
