<?php
ob_start();
?>

<p class="item_dear">Cher <?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>Suite à votre demande auprès du service support de <?= $sso ?> le <?= $this->getOpenDate() ?>, nous vous informons que votre cas est traité par notre support technique. Veuillez indiquer le numéro de dossier TOC#<?= $this->itsID ?> dans toute autre communication concernant cette demande.</p>
<p>Nous vous tiendrons informé dés lors que votre problème sera résolu. Vous pouvez également recevoir des rapports intermédiaires faisant état des différentes actions entreprises.</p>
<p>Notre conseiller '<?= $sso ?> On Call' reste à votre disposition pour tout autre renseignements</p>
<p>Cordialement,</p>
<p><?= $sso ?> Service Team</p>

<?php
$_output = ob_get_contents();
ob_end_clean();
return $_output
?>
