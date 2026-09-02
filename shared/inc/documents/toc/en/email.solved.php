<?php
ob_start();
?>

<p class="item_dear">Dear <?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>Following-up on your request to "<?= $sso ?> on Call" on <?= $this->getOpenDate() ?><?php if($this->isEquipmentSet()): ?> about your equipment <?= $this->itsHeader['type'] ?> at <?= $this->itsHeader['airport_code']?> airport<?php endif; ?>, we inform you that your case TOC#<?= $this->itsID?> is now solved. You can access further details <a href="https://www.tld-gse.com/extranet/index.php?m[0]=toc&m[1]=view&id=<?= $this->itsID ?>">here</a>.</p>
<p>We do hope our service delivery has been satisfactory and would appreciate if you could take a moment to rate your experience <a href="<?= $tokenLink ?>">here</a> (1 minute)</p>
<p>Should you have any query pending, please contact us at your earliest convenience. If we do not hear or read from you within the next 14 days we would consider the case definitely closed.</p>
<p>We thank you for relying on <?= $sso ?> for your GSE operation.</p>
<p>Sincerely,</p>
<p><?= $sso ?> Service Team</p>

<?php
return ob_get_clean();
?>
