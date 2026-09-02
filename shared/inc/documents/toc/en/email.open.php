<?php
ob_start();
?>

<p class="item_dear">Dear <?= $this->itsContact->getFirstname() ?> <?= $this->itsContact->getLastname() ?>,</p>
<p>Following-up on your request to <?= $sso ?> Support Services on <?= $this->getOpenDate() ?>, we inform you that your case is being addressed by our Service Team. Please reference your case number TOC#<?= $this->itsID ?> in any further communication about this request. </p>
<p>We will inform you upon solving of your issue. You may also receive intermediary reports stating the various actions undertaken.</p>
<p>Our ‘<?= $sso ?> On Call’ advisor remains available should you have any other questions or inquiries.</p>
<p>Sincerely,</p>
<p><?= $sso ?> Service Team</p>

<?php
return ob_get_clean();
?>
