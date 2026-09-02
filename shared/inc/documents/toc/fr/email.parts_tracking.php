<?php
	ob_start();
?>

<p class="item_dear">Cher <?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>Concernant votre demande de support enregistré comme TOC#<?= $this->itsHeader['id'] ?>, nous vous informons que les colis de pièces suivants ont été expédiés:</p>

<ul>
    <li>Bon de livraison : #<?= $partsTracking['packing_slip'] ?></li>
    <li>Suivi: #<?= $partsTracking['tracking'] ?></li>
    <?php foreach ($partsTracking['parts'] as $parts): ?>
        <ul>
            <li>Numéro de pièce : #<?= $parts['part_number'] ?></li>
            <li>Description : <?= $parts['description'] ?></li>
            <li>Qauntité expediée : <?= $parts['quantity'] ?></li>
        </ul>
        <br>
    <?php endforeach; ?>
</ul>

<p>Notre conseiller 'TLD On Call'  reste à votre disposition pour tout autre renseignements</p>
<p>Cordialement,</p>
<p>TLD Service Team</p>

<?php
return ob_get_clean();
?>
