<?php
ob_start();
?>

<p class="item_dear">Cher(e) <?= mb_convert_encoding($this->itsContact->getFirstname(), 'UTF-8', mb_list_encodings()) ?> <?= mb_convert_encoding($this->itsContact->getLastname(), 'UTF-8', mb_list_encodings()) ?>,</p>
<p>A la suite de votre requête faite auprès de nos équipes "<?=$sso?> on Call" le <?= $this->getOpenDate() ?> nous vous indiquons ci-après les premiers éléments d'information relatifs à votre équipement:</p>
<p><b><?= $txt1 ?></b></p>
<p>Nous souhaitons vous apporter le meilleur support possible. N'hésitez pas à visiter notre site web <a href="https://www.tld-gse.com/extranet">support website</a> pour y trouver toutes les informations sur vos produits, leurs manuels et encore beaucoup d'autres informations.</p>
<p>N'hésitez pas également à nous appeler, nos équipes « <?=$sso?> on Call » sont à votre service pour tous renseignements dont vous pourriez avoir besoin.</p>
<p>Cordialement,</p>
<p><?=$sso?> Service Team</p>

<?php
return ob_get_clean();
?>
