<?php
ob_start();
?>

<p class="item_dear">Cher <?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>Suite à votre demande auprès ‘<?= $sso ?> on Call' le <?= $this->getOpenDate() ?><?php if($this->isEquipmentSet()): ?>concernant votre équipement <?= $this->itsHeader['type'] ?> à l'aéroport <?= $this->itsHeader['airport_code'] ?><?php  endif; ?>, nous avons le plaisir de vous informer que votre  demande référencée  TOC#<?= $this->itsID ?> est maintenant résolue. Vous pouvez obtenir de plus amples renseignements à l'adresse <a href="https://www.tld-gse.com/extranet/index.php?m[0]=toc&m[1]=view&id=<?= $this->itsID ?>">suivante</a>.</p>
<p>Nous espérons que notre prestation de service a été satisfaisante et nous vous serions reconnaissants de bien vouloir prendre un moment pour évaluer votre expérience <a href="<?= $tokenLink ?>">ici</a> (1 minute).</p>
<p>Si vous aviez des questions en suspens, vous pouvez  nous contacter dans les plus brefs délais. Sans retour de votre part sous 14 jours, nous considérerons votre requête comme définitivement close.</p>
<p>Nous vous remercions pour votre confiance</p>
<p>Cordialement,</p>
<p><?= $sso ?> Service Team</p>

<?php
return ob_get_clean();
?>
